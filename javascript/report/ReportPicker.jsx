/**
    Component that helps with the reports for SLC
**/
var ReportPickerList = React.createClass({
    getInitialState: function() {
        return {
            htmlData: null,
            dropDownValue: -1,
            startDate: "12/13/1901",
            endDate: "01/18/2038",
            errorWarning: ''
        };
    },
    getHtmlData: function(dropValue){
        $.ajax({
            url: 'index.php?module=slc&action=GETReportHTML&report_type='+dropValue
                +'&startDate='+this.state.startDate+'&endDate='+this.state.endDate,
            type: 'GET',
            dataType: 'json',
            success: function(data) {    
                this.setState({htmlData: data});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab report picker data.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });
    },
    getCSVData: function(name)
    {  
        // Opens a new window so that a csv file can be downloaded.
        window.location = 'index.php?module=slc&action=GETReportCSV&startDate=' + this.state.startDate
                + '&endDate=' + this.state.endDate + '&report_type=' + name;
    },
    handleDates: function(time, date) {
        //time being start/end
        if (time == 'start')
        {
            if (date == '')
            {
                // If the start field is empty, set it to default.
                this.setState({startDate: "12/13/1901"});
            }
            else
            {
                this.setState({startDate: date});
            }
        }
        else if (time == 'end')
        {
            if (date == '')
            {
                // If the end field is empty, set it to default.
                this.setState({endDate: "01/18/2038"});
            }
            else
            {
                this.setState({endDate: date});
            }
        }
        // Used to update the table if the user wishes to change the dates.
        this.handleDrop();
    },
    handleDrop: function() {
        var reportValue = React.findDOMNode(this.refs.reportPicker).value;

        // Checks to see the value of the dropdown.
        if ( reportValue != -1)
        {
            this.getHtmlData(reportValue);
            this.setState({dropDownValue: reportValue});
        }
        else
        {
            this.setState({htmlData: null,
                           dropDownValue: -1});
        }
    },
    render: function() {
        return (
            <div>
                <h3>Issue Tracker Reporting System</h3>

                <div className="row">
                    <div className="col-md-4">
                        <form role="form">
                            <div className="form-group">
                                <label>Select report:</label>
                               
                                <select className="form-control" onChange={this.handleDrop} ref="reportPicker">
                                    <option value="-1">[ choose report type ]</option>
                                    <option value="AppointmentStats">Appointment Statistics</option>
                                    <option value="IntakeProblemType">Intake by Problem Type</option>
                                    <option value="LandlordTenant">Landlord Tenant</option>
                                    <option value="ConditionByLandlord">Condition by Landlord</option>
                                    <option value="ProblemByYear">Problem by Year in School</option>
                                    <option value="TypeOfCondition">Type of Condition</option>
                                    <option value="TypeOfReferral">Type of Referral</option>
                                    <option value="LawByAgency">Problems With Law Enforcement by Agency</option>
                                    <option value="TransferIntern">Transfer and International Statistics</option>
                                </select>
                            
                       
                                <br />
                                <label>Date of visit:</label>
                                <br />
                                <DatePickerBox handleDates = {this.handleDates} date="start" />
                                <label>to</label>
                                <br />
                                <DatePickerBox handleDates = {this.handleDates} date="end" />
                            </div>
                        </form>

                    </div>
                </div>

                <hr />

                <InsertHtml html = {this.state.htmlData}
                            dropDownValue = {this.state.dropDownValue}
                            getCSVData = {this.getCSVData} />
                
            </div>
        );
    }
});


/**
    Component helps grab the raw html from the template.
    It also enables csv and the buttons.
**/
var InsertHtml = React.createClass({   
    handleApptStats: function() {
        this.props.getCSVData('AppointmentStats');
    },
    handleLandlordTenant: function() {
        this.props.getCSVData('LandlordTenant');
    },
    handleConditionsByLand: function() {
        this.props.getCSVData('ConditionByLandlord');
    },
    render: function() {
        var val = this.props.dropDownValue;
        // Determines the html that goes into the report.
        if (this.props.html == null)
        {
            var HTML =  <div id="report-area" style={{margin: "15px"}}>
                            Once a report is generated, it will appear here.
                        </div>
        }
        else
        {
            var HTML =  <div id="report-area" style={{margin: "15px"}} dangerouslySetInnerHTML={this.props.html} />
        } 

        var button = <div></div>;

        // Determines if the csv buttons should be enabled based on the report type.
        switch (val)
        {
            case 'AppointmentStats':
                button = <button className="btn btn-default pull-right" type="submit" onClick={this.handleApptStats}>CSV Export</button>
                break;
            case 'LandlordTenant':
                button = <button className="btn btn-default pull-right" type="submit" onClick={this.handleLandlordTenant}>CSV Export</button>
                break;
            case 'ConditionByLandlord':
                button = <button className="btn btn-default pull-right" type="submit" onClick={this.handleConditionsByLand}>CSV Export</button>
                break;
        }
        return (
            <div>
                {button}
                {HTML}
            </div>
        );
    }
});


/**
    This component is used for the date picker.
    [This is using jquery]
**/
var DatePickerBox = React.createClass({
    _initDatePicker: function() {
        var element = this.getDOMNode();

        $(element).datepicker({
            autoclose: true
        });

        var onChangeDate = this.handleDate;

        $(element).on('change', function(e){
            e.stopPropagation();
            e.preventDefault();
            onChangeDate();
        });
    },
    componentDidMount: function() {
        this._initDatePicker();
    },
    componentWillUnmount: function() {
        var element = this.getDOMNode();
        $(element).datepicker("destroy");
    },
    handleDate: function(){      
        var dPicker = this.getDOMNode().value.trim();
        this.props.handleDates(this.props.date, dPicker);
    },
    render: function() {
        return (

                <input type="text" className="form-control" ref="date_picker" />
        );
    }
});

React.render(
    <ReportPickerList />,
    document.getElementById('content')
);
