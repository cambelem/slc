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
            url: 'index.php?module=slc&action='+dropValue+'&startDate='+this.state.startDate+'&endDate='+this.state.endDate,
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
    handleDates: function(time, date) {
        //time being start/end
        if (time == 'start')
        {
            if (date == '')
            {
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

        if ( reportValue != -1)
        {
            this.getHtmlData(reportValue);
        }
        else
        {
            this.setState({htmlData: null});
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
                                 <div className="col-md-10">
                                    <select className="form-control" onChange={this.handleDrop} ref="reportPicker">
                                        <option value="-1">[ choose report type ]</option>
                                        <option value="GETAppointmentStats">Appointment Statistics</option>
                                        <option value="GETIntakeProblemType">Intake by Problem Type</option>
                                        <option value="GETLandlordTenant">Landlord Tenant</option>
                                        <option value="GETConditionByLandlord">Condition by Landlord</option>
                                        <option value="GETProblemByYear">Problem by Year in School</option>
                                        <option value="GETTypeOfCondition">Type of Condition</option>
                                        <option value="GETTypeOfReferral">Type of Referral</option>
                                        <option value="GETLawByAgency">Problems With Law Enforcement by Agency</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div className="form-group">
                                <label >Date of visit:</label>
                                <br />

                                <DatePickerBox handleDates = {this.handleDates} date="start" />

                                <label>to </label>
                                <br />

                                <DatePickerBox handleDates = {this.handleDates} date="end" />
                            </div>
                        </form>

                    </div>
                </div>

                <hr />

                <InsertHtml html = {this.state.htmlData} />
                
            </div>
        );
    }
});

var InsertHtml = React.createClass({
    render: function() {
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
        return (
            HTML
        );
    }
});

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
    handleDate: function(){      
        var dPicker = React.findDOMNode(this.refs.date_picker).value.trim();
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
