// !!The banner_id variable is important!!

// It's being used as a global variable from the head.js where this file is located
// to determine which banner id is being used so it can grab the client data.

var ViewClientMain = React.createClass({
    getInitialState: function() {
        return {
            clientData: null,
            errorWarning: ''
        };
    },
    componentWillMount: function(){
        this.getClientData();
    },
    getClientData: function(){
        $.ajax({
            url: 'index.php?module=slc&action=GetStudentClientData&banner_id=' + banner_id,
            type: 'GET',
            dataType: 'json',
            success: function(data) { 
                console.log(data);   
                this.setState({clientData: data});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab client data.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });
    },
    newVisit: function(){
        $.ajax({
            url: 'index.php?module=slc&action=POSTNewVisit&banner_id=' + this.state.clientData.client.id,
            type: 'POST',
            dataType: 'json',
            success: function(data) { 
                console.log(data);
                window.location = "index.php?module=slc&view=NewIssue&visitid="+data.visitID+"&cname="+ this.state.clientData.client.name;
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to go to new visit.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)                
        });  
    },
    render: function() {
        
        if (this.state.clientData == null)
        {
            var client = null;
            return (<div></div>);
        }
        else
        {
            var client = this.state.clientData.client;
            var visit = this.state.clientData.visit;
            var getClient = this.getClientData;

            var visits = visit.map(function (data) {            
                return (
                    <ViewVisits key = {data.id}
                    id = {data.id}
                    init_date = {data.initial_date}
                    issues = {data.issues} 
                    getClient = {getClient}
                    client = {client} />
                );
            });
        }
        return (
            <div>
                <div id="CLIENT_ID" style={{display:"none"}}>{client.id}</div>
                <div className="row">
                    <div className="col-md-6">
                        <h1 id="client_name">{client.name}</h1>
                        <h3 id="client_info">{client.classification} - {client.major}</h3>
                    </div>
                    <div className="col-md-6">
                        <span id="first_visit" className="pull-right">First Visit: {client.first_visit}</span>
                    </div>
                </div>

                

                <div className="row">
                    <div className="col-md-12">
                        <form className="form-horizontal" role="form">
                            {client.referralString}
                        </form>
                    </div>
                </div>

                <div className="row" style={{borderTop: "1px solid #CCC", marginTop: "1em"}}>
                    <div className="col-md-6" style={{marginTop: "1em"}}>
                        <span className="pull-left">Visits:</span>
                    </div>

                    <div className="col-md-6" style={{marginTop: "1em"}}>
                        <button type="button" id="NEWVISIT" className="btn btn-primary pull-right" onClick={this.newVisit}><i className="fa fa-plus"></i> New Visit</button>
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

var ViewVisits = React.createClass({
    getInitialState: function() {
        return {
            errorWarning: ''
        };
    },
    render: function() {
        var link = 'index.php?module=slc&view=NewIssue&visitid='+this.props.id+"&cname="+this.props.client.name;
        var newIssue = <a href={link}>NEW ISSUE</a>

        var getClient = this.props.getClient;
        var issues = this.props.issues.map(function (data) {             
            return (
                <ViewIssues 
               id = {data.id}
               last_access = {data.last_access}
               counter = {data.counter}
               landlord_name = {data.landlord_name}
               visit_issue_id = {data.visit_issue_id}
               getClient = {getClient}
               name = {data.name}/>
            );
        });
        return (
            <div style={{marginBottom: "1.5em"}}>
                <div className="row" style={{marginBottom: "1em"}}>
                    <div className="col-md-12" style={{borderBottom: "1px solid #CCC", marginBottom: "1em"}}>
                        <span style={{"fontWeight":'bold'}}>{this.props.init_date}</span>
                        <span style={{"position":'relative', "fontSize":10, "right":-5,"fontWeight":100, "paddingTop":10}}>
                            <span style={{"fontSize":12,"fontWeight":'bold'}}>[</span> 
                                {newIssue}
                            <span style={{"fontSize":12,"fontWeight":'bold'}}>]</span>
                        </span>
                    </div>  
                </div>
                {issues}  
            </div> 
        );
    }
});

var ViewIssues = React.createClass({
    getInitialState: function() {
        return {
            errorWarning: ''
        };
    },
    handleFollowUp: function() {
        var v_id = this.props.visit_issue_id;
        $.ajax({
            url: 'index.php?module=slc&action=POSTIncrementVisit&visit_issue_id='+v_id,
            type: 'POST',
            dataType: 'json',
            success: function() { 
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

                    <div className="col-md-1">
                        <span className="pull-left">{this.props.counter} visit(s)</span>
                    </div>

                    <div className="col-md-2 col-md-offset-2">
                        <button type="button" className="btn btn-default btn-sm pull-right" onClick={this.handleFollowUp}><i className="fa fa-plus"></i> Follow Up</button>
                    </div>  
            </div>

        );
    }
});

React.render(
    <ViewClientMain />,
    document.getElementById('clientviews')
);
