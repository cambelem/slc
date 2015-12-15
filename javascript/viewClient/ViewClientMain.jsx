// !!The banner_id variable is important!!

// It's being used as a global variable from the head.js. 
// It determine's which banner id is being used so it can grab the client data.


// ReactBootstrap
var ModalTrigger = ReactBootstrap.ModalTrigger;
var Button = ReactBootstrap.Button;
var Modal = ReactBootstrap.Modal;

var ViewClientMain = React.createClass({
    getInitialState: function() {
        return {
            clientData: null,
            issueTreeData: null,
            referralData: null,
            msgNotification: '',
            msgType: '',
            notificationData: null
        };
    },
    componentWillMount: function(){
        this.getClientData();
        this.getReferralData();
    },
    componentDidMount: function(){
        this.getIssueData();
    },
    getClientData: function(){
        $.ajax({
            url: 'index.php?module=slc&action=GETStudentClientData&banner_id=' + banner_id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {   
                this.setState({clientData: data});           
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab client data."+err.toString());
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });
    },
    getIssueData: function() {
        $.ajax({
            url: 'index.php?module=slc&action=GETNewIssue',
            type: 'GET',
            dataType: 'json',
            success: function(data) { 
                var landlordTypes = data.tree;
                var landlords = data.landlords;

                // Adds a -1 value to be used later in the  
                // dropdowns in the Modal form.
                for (var type in landlordTypes)
                {
                    landlordTypes[type].unshift({problem_id:-1, name:"Select an Issue"});
                }

                landlords.unshift({id:-1, name:"Select a Landlord"});

                this.setState({issueTreeData: data});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab client data.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });    
    },
    getReferralData: function() {
        $.ajax({
            url: 'index.php?module=slc&action=GETReferralBox',
            type: 'GET',
            dataType: 'json',
            success: function(data) { 
                var referral = data.referral_picker;
                // Adds a -1 value to be used in the referral dropdown.
                referral.unshift({referral_id:-1, name:"Select a Referral"});
                this.setState({referralData: data});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab referral data.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });    
    },
    newVisit: function(issueData){
        var visitIssueData = JSON.stringify(issueData);
        $.ajax({
            url: 'index.php?module=slc&action=POSTNewVisit&banner_id=' + this.state.clientData.client.id,
            type: 'POST',
            data: visitIssueData,
            dataType: 'json',
            success: function(msg) { 
                // Grabs the client data for re-render of new visit.
                this.getClientData();
                // Determines the notification message.
                var key = Object.keys(msg);
                this.setState({msgNotification: msg[key],
                               msgType: key,
                               notificationData: issueData});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to go to new visit.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });  
    },
    postReferral: function(r_id) {
        $.ajax({
            url: 'index.php?module=slc&action=POSTReferralType&banner_id=' + this.state.clientData.client.id + '&referral_type=' + r_id,
            type: 'POST',
            dataType: 'json',
            success: function(data) { 
                // Grabs the client data for re-render of the new Referral.
                this.getClientData();
                // Sets notification message for successful referral change.
                this.setState({msgNotification: "Successfully changed the referral type.",
                                msgType: "success",
                                notificationData: null});
            }.bind(this),
            error: function(xhr, status, err) {
                // Sets notification message for failed referral change.
                this.setState({msgNotification: "Failed to change the referral type. " + err.toString(),
                               msgType: "error",
                               notificationData: null});
            }.bind(this)                
        });          
    },
    postEmail: function() {
        $.ajax({
            url: 'index.php?module=slc&action=POSTSendMail&banner_id=' + banner_id + '&name=' + this.state.clientData.client.name,
            type: 'POST',
            dataType: 'json',
            success: function() { 
            }.bind(this),
            error: function(xhr, status, err) {
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });          
    },
    render: function() {
        if (this.state.clientData == null)
        {
            var client = null;
            // While the server is waiting for the data, display
            // a spinning loading wheel.
            return (<div style={{top:"50%", left: "50%", position:"absolute"}}>
                        <i className="fa fa-spinner fa-pulse fa-5x"></i>
                    </div>);
        }
        else
        {
            var client = this.state.clientData.client;
            var visit = this.state.clientData.visit;
            var newIssue = this.newIssue;
            var getClient = this.getClientData;

            // Displays all available visits for a given client.
            var visits = visit.map(function (data) {            
                return (
                    <ViewVisits key         = {data.id}
                                id          = {data.id}
                                init_date   = {data.initial_date}
                                issues      = {data.issues} 
                                getClient   = {getClient}
                                newIssue    = {newIssue}
                                client      = {client} />
                );
            });
        }

        if (this.state.referralData == null)
        {
            // Display nothing until the referral request passes.
            var referral = null;
            return (<div></div>);
        }
        else
        {
            var referral =  <ReferralStatus referralData = {this.state.referralData.referral_picker}
                                referralString = {client.referralString}
                                postReferral = {this.postReferral} />
        }
        return (
            <div>
                <div id="CLIENT_ID" style={{display:"none"}}>{client.id}</div>

                <Notifications msg = {this.state.msgNotification}
                               msgType = {this.state.msgType}
                               notificationData = {this.state.notificationData} />

                <div className="row">
                    <div className="col-md-6">
                        <h1 id="client_name">{client.name}</h1>
                        <h3 id="client_info">{client.classification} - {client.major}</h3>
                    </div>
                    <div className="col-md-6">
                        <span id="first_visit" className="pull-right">First Visit: {client.first_visit}</span>
                    </div>
                </div>        


                {referral}

                <div className="row" style={{borderTop: "1px solid #CCC", marginTop: "1em"}}>
                    <div className="col-md-6" style={{marginTop: "1em"}}>
                        <span className="pull-left">Visits:</span>
                    </div>

                    <div className="col-md-6" style={{marginTop: "1em"}}>
                        <ModalTrigger modal={<ModalForm issueTreeData = {this.state.issueTreeData} 
                                                        newVisit      = {this.newVisit} 
                                                        sendEmail     = {this.postEmail} />}>
                            <Button bsStyle='primary' className="pull-right"><i className="fa fa-plus"></i> New Visit</Button>
                        </ModalTrigger>
                    </div>               
                </div>

                <div className="row">
                    <div className="col-md-12">
                        {visits}
                    </div>
                </div>
            </div>
        );
    }
});


/**
    Notification component used with a successful visit or
    referral change.
**/
var Notifications = React.createClass({
    render: function(){
        var notification;
        // Determine if the screen should render a notification.
        if (this.props.msg != '')
        {
            if (this.props.msgType == 'success')
            {
                // Used for visits.
                if (this.props.notificationData != null)
                {
                    notification = <div className="alert alert-success" role="alert">
                                    <strong>{this.props.msg} </strong> 
                                    <br />
                                    
                                    <ul>
                                        {this.props.notificationData.map(function (key) {          
                                            return (
                                                <ListIssues key    = {key.name}
                                                            name   = {key.name}
                                                            llID   = {key.llID} 
                                                            id     = {key.id}
                                                            llName = {key.llName} />
                                            );
                                        })}
                                    </ul>
                                </div>
                }
                else // Used for referrals.
                {
                    notification = <div className="alert alert-success" role="alert">
                                    <strong>{this.props.msg}</strong> 
                                    <br />  
                                </div>
                }
            }
            else if (this.props.msgType == 'error')
            {
                notification = <div className="alert alert-danger" role="alert">
                                    <strong>Error: </strong> 
                                    <br />
                                    {this.props.msg}
                                </div>
            }
        }
        else
        {
            notification = '';
        }
        return (
            <div>{notification}</div>
        );
    }
});


/**
    Component that lists all the issues for a given visit.
**/
var ListIssues = React.createClass({
    render: function(){
        if (this.props.llID != null)
        {
            conditions = <span>{this.props.name} <em> with </em> {this.props.llName}</span>;
        }
        else
        {
            conditions = <span>{this.props.name} </span>;
        }
        return (

            <li>{conditions}</li>
        );
    }
});

/**
    Component that determines the referral status of the client.
**/
var ReferralStatus = React.createClass({
    handleReferral: function(e){
        // Grabs the value from the referral dropdown box.
        var r_id = e.target.value;
        this.props.postReferral(r_id);
    },
    render: function(){
        var referralData = this.props.referralData;
        var referralString = this.props.referralString;
        var referralNotice;

        if (referralString == null)
        {
            // Tells the user with a small yellow icon to choose a referral type.
            referralNotice = <abbr title="Please select a referral">
                                <span className="pull-right">
                                    <i className="fa fa-exclamation-triangle" style={{color: "gold"}}></i>
                                </span>
                            </abbr>
        }
        else
        {
            referralNotice = <div></div>
        }
        return (
            <div className="row">
                <div className="col-md-3">
                    <form className="form-horizontal" role="form">
                        Referral: {referralNotice}

                        <select className="form-control" onChange={this.handleReferral}>
                            {referralData.map(function (key) {          
                                return (
                                    <ProblemList key            = {key.name}
                                                 name           = {key.name}
                                                 id             = {key.referral_id}
                                                 referralString = {referralString} />
                                );
                            })}
                        </select>

                    </form>
                </div>
            </div>
        );
    }
});

/**
    Component that helps view the visits for a given user.
**/
var ViewVisits = React.createClass({
    render: function() {
        var getClient = this.props.getClient;
        var issues = this.props.issues.map(function (data) {             
            return (
                <ViewIssues key             = {data.name}
                            id              = {data.id}
                            last_access     = {data.last_access}
                            counter         = {data.counter}
                            landlord_name   = {data.landlord_name}
                            visit_issue_id  = {data.visit_issue_id}
                            getClient       = {getClient}
                            name            = {data.name}/>
            );
        });
        return (
            <div style={{marginBottom: "1.5em"}}>
                <div className="row" style={{marginBottom: "1em"}}>
                    <div className="col-md-12" style={{borderBottom: "1px solid #CCC", marginBottom: "1em"}}>
                        <span style={{"fontSize": 18, "fontWeight":'bold'}}>{this.props.init_date}</span>
                    </div>  
                </div>
                {issues}  
            </div> 
        );
    }
});

/**
    Component that creates and controls each issue within a visit.
    This also creates the followup button with its given event handler.
**/
var ViewIssues = React.createClass({
    handleFollowUp: function() {
        var v_id = this.props.visit_issue_id;
        $.ajax({
            url: 'index.php?module=slc&action=POSTIncrementVisit&visit_issue_id='+v_id,
            type: 'POST',
            dataType: 'json',
            success: function() { 
                // Rerender the screen for added changes to the follow-up.
                this.props.getClient();
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to increment.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });
    },
    render: function() {
        return (
            <div className="row" style={{marginBottom: "1.5em"}}>
                <div className="col-md-7" >
                    {this.props.name}<span style={{'fontStyle':'italic'}}> with </span>{this.props.landlord_name}    

                    <br />

                    <div style={{borderBottom: "1px solid #CCC"}} />
                    <span style={{fontStyle:"italic", fontSize:"10px"}}>Last Accessed {this.props.last_access}</span> 
                </div> 

                <div className="col-md-2">
                    <span className="pull-left">{this.props.counter} follow up(s)</span>
                </div>

                <div className="col-md-1 col-md-offset-2">
                    <button type="button" className="btn btn-default btn-sm pull-right" onClick={this.handleFollowUp}><i className="fa fa-plus"></i> Follow Up</button>
                </div>  
            </div>
        );
    }
});

/**
    Component used by the notification and dropdown components
    Helps create option tags.
**/
var ProblemList = React.createClass({
  render: function() {  
    var list = '';

    if (this.props.referralString != null)
    {
        if (this.props.name == this.props.referralString)
        {
            // Selects a pre-chosen value already given by the database.
            list = <option value={this.props.id} selected>{this.props.name}</option> 
        }
        else
        {
            list = <option value={this.props.id}>{this.props.name}</option> 
        }
          
    }
    else
    {
        // Used by the notification/issue dropdowns
        list = <option value={this.props.id}>{this.props.name}</option> 
    }
    return (    
        list
    )
  }
});

React.render(
    <ViewClientMain />,
    document.getElementById('clientviews')
);
