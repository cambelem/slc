// !!The banner_id variable is important!!

// It's being used as a global variable from the head.js. 
// It determine's which banner id is being used so it can grab the client data.

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
                
                this.getClientData();

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
                this.getClientData();

                this.setState({msgNotification: "Successfully changed the referral type.",
                                msgType: "success",
                                notificationData: null});
            }.bind(this),
            error: function(xhr, status, err) {
                this.setState({msgNotification: "Failed to change the referral type. " + err.toString(),
                               msgType: "error",
                               notificationData: null});
            }.bind(this)                
        });          
    },
    render: function() {
        
        if (this.state.clientData == null)
        {
            var client = null;
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
                        <ModalTrigger modal={<ModalForm issueTreeData={this.state.issueTreeData} newVisit={this.newVisit}/>}>
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

var Notifications = React.createClass({
    render: function(){
        var notification;
        if (this.props.msg != '')
        {
            if (this.props.msgType == 'success')
            {
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
                else
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

var ReferralStatus = React.createClass({
    handleReferral: function(e){
        var r_id = e.target.value;

        this.props.postReferral(r_id);

    },
    render: function(){
        var referralData = this.props.referralData;
        var referralString = this.props.referralString;
        var referralNotice;

        if (referralString == null)
        {
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


var ViewIssues = React.createClass({
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
                    <span className="pull-left">{this.props.counter} follow up(s)</span>
                </div>

                <div className="col-md-2 col-md-offset-2">
                    <button type="button" className="btn btn-default btn-sm pull-right" onClick={this.handleFollowUp}><i className="fa fa-plus"></i> Follow Up</button>
                </div>  
            </div>
        );
    }
});

// !!This uses ReactBootstrap!!
var ModalForm = React.createClass({
    getInitialState: function() {
        return {
            issueData: [],
            type: '',
            selectedVal: -1,
            selectedLL: -1
        };
    },
    handleSave: function(){
        // Used so that the new visit modal doesn't close if nothings selected.
        if (this.state.issueData.length !== 0)
        {
            this.props.newVisit(this.state.issueData);
            this.props.onRequestHide();
        }
    },
    removeItem: function(id){
        var newIssueData = this.state.issueData.filter(function (el){
            return el.id !== id;
            });

        this.setState({issueData: newIssueData});   
    },
    handleTenant: function(){
        this.setState({type: 'LandlordTenant',
                       selectedVal: -1,
                       selectedLL: -1});
    },
    handleCriminal: function(){
        this.setState({type: 'Criminal',
                       selectedVal: -1,
                       selectedLL: -1});
    },
    handleOther: function(){
        this.setState({type: 'Other',
                       selectedVal: -1,
                       selectedLL: -1});
    },
    displaySelectedIssues: function(issues){ //May not be used later?
        this.setState({issueData: issues});
    },
    searchIndexOf: function(searchTerm){
        for(var i = 0; i < this.state.issueData.length; i++)
        {
            if(this.state.issueData[i]['id'] === searchTerm)
                return i;
        }
        return -1;
    },
    searchObjectIndexOf: function(type, landlord){
        var isTypeThere = false;
        for(var i = 0; i < this.state.issueData.length; i++)
        {
            if(this.state.issueData[i]['id'] === type)
            {
                return i;
            }

            if(this.state.issueData[i]['id'] === type && 
               this.state.issueData[i]['llID'] === landlord &&
               !isTypeThere)
            {
                return i;
            }
        }
        return -1;
    },
    setData: function(data){
        this.setState({issueData: data})
    },
    findProblemName: function(pid){
        // types is this.props.issueTreeData.tree;
        var types = this.props.issueTreeData.tree;
        for (var type in types)
        {
            for (var key in types[type])
            {
                if (pid ==  types[type][key].problem_id)
                {
                    return types[type][key].name;
                }
            }
        }       
    },
    findLandlordName: function(llid){
        var landlords = this.props.issueTreeData.landlords;
        for (var landlord in landlords)
        {
            if (llid == landlords[landlord].id)
            {
                return landlords[landlord].name;
            }
        }
    },
    handleAdd: function() { //ProblemTypeDrop
        var type = this.state.selectedVal;
        var landlord = this.state.selectedLL;
        var items = this.state.issueData;

        if (type != -1 && landlord == -1)
        {
            if (this.searchIndexOf(type) == -1)
            {
                items.push({id:type,
                            name:this.findProblemName(type)});
                this.setState({issueData: items});
            }
        }

    },
    handleLandlordAdd: function() {
        var type = this.state.selectedVal;
        var landlord = this.state.selectedLL;
        var items = this.state.issueData;

        if (type != -1 && landlord != -1)
        {
            if (this.searchObjectIndexOf(type, landlord) == -1)
            {    
                items.push({id:type, 
                            name:this.findProblemName(type),
                            llID:landlord,
                            llName:this.findLandlordName(landlord)});
                this.setState({issueData: items});
            }
        }
    },
    selectedVal: function(val, ll) {
        this.setState({selectedVal: val});
    },
    selectedLL: function(ll) {
        this.setState({selectedLL: ll});
    },
    render: function() {
        var types = this.props.issueTreeData.tree;
        var landlords = this.props.issueTreeData.landlords;
        var removeItem = this.removeItem;
        var dData = '';

        if (this.state.type != '')
        {
            if (this.state.type == 'LandlordTenant')
            {
                dData = <LandlordDrop  landlordsTypes = {types.LandlordTenant}
                                       landlords      = {landlords} 
                                       conditions     = {types.Conditions} 
                                       issueData      = {this.state.issueData}
                                       selectedVal    = {this.selectedVal}
                                       selectedLL     = {this.selectedLL} />;
                add = <button className="btn btn-default" type="submit" onClick={this.handleLandlordAdd}>Add</button>
            }
            else if (this.state.type == 'Other')
            {
                dData = <ProblemTypeDrop  type      = {types.Other}
                                          issueData = {this.state.issueData} 
                                          selectedVal = {this.selectedVal} />;
                add = <button className="btn btn-default" type="submit" onClick={this.handleAdd}>Add</button>
            }
            else
            {
                dData = <ProblemTypeDrop  type      = {types.Criminal}  
                                          issueData = {this.state.issueData}
                                          selectedVal = {this.selectedVal} />;
                add = <button className="btn btn-default" type="submit" onClick={this.handleAdd}>Add</button>
            }          
        }  
        else
        {
            add = <div></div>
        }
        return (
          <Modal {...this.props} bsSize="large" backdrop='static' title='New Visit' animation={true}>
            <div className="col-md-12">
                <div className='modal-body'>
                    <div id="CLIENT_ID" style={{display:"none"}}></div>
                
                        <div style={{display:"block"}} id="committedIssues">
                            <h3 style={{borderBottom:"1px solid #272727"}}>Issues to be Added: </h3>
                        </div>
                        <span style={{display:"block",marginTop:"8px",marginBottom:"8px"}}>
                            <ShowSelectedIssues issueData  = {this.state.issueData} 
                                                removeItem = {removeItem} 
                                                setData    = {this.setData} />        
                        </span>

                    <h2>Problems</h2>
                    
                    <hr />
                    
                    <br />

                    <div className="btn-group" data-toggle="buttons" >
                        <label className="btn btn-default" onClick={this.handleTenant} >
                            <input type="radio" name="types" /> Landlord-Tenant / Condition
                        </label>      

                        <label className="btn btn-default" onClick={this.handleCriminal} >
                            <input type="radio" name="types" /> Criminal
                        </label>    

                        <label className="btn btn-default" onClick={this.handleOther} >
                            <input type="radio" name="types" /> Other
                        </label>    
                    </div>

                    <br /><br /><br />
                    <div className="form-group">
                        {dData}

                        <div className="col-md-3">
                            {add}
                        </div>
                    </div>
                    <br /><br /><br /><br />
                </div>
            </div>

            <div className='modal-footer'>
              <Button onClick={this.props.onRequestHide}>Close</Button>
              <Button bsStyle='primary' onClick={this.handleSave}>Save Visit</Button>
            </div>
          </Modal>
        );
    }
});

var ShowSelectedIssues = React.createClass({
    render: function() {
        var removeItem = this.props.removeItem;
        return (
            <div className="row">
                <div className="col-md-6">
                    <ul className="list-group">
                        {this.props.issueData.map(function (key) {          
                            return (
                                <PrintIssues key        = {key.id}
                                             name       = {key.name}
                                             llID       = {key.llID} 
                                             id         = {key.id}
                                             llName     = {key.llName}
                                             removeItem = {removeItem} />
                            );
                        })}
                    </ul>
                </div>
            </div>
        );
    }
});

var PrintIssues = React.createClass({
    handleRemove: function() {
        this.props.removeItem(this.props.id);
    },
    render: function() {   
    var conditions;
    
    if (this.props.llID != null)
    {
        conditions = <span>{this.props.name} <em> with </em> {this.props.llName} 
                         <a onClick={this.handleRemove} > <i className="fa fa-trash-o pull-right close"></i></a>
                    </span>;
    }
    else
    {
        conditions = <span>{this.props.name} 
                         <a onClick={this.handleRemove} > <i className="fa fa-trash-o pull-right close"></i></a>
                     </span>;
    }
        return (

            <li className="list-group-item">{conditions}</li>

        );
    }
});

var ProblemTypeDrop = React.createClass({
    handleTypes: function(e) {
        this.props.selectedVal(e.target.value, -1);
    },
    render: function() {
        return (
            <div>
                <div className="col-md-3">
                    <select className="form-control" onChange={this.handleTypes}>
                        {this.props.type.map(function (key) {          
                            return (
                                <ProblemList key    = {key.problem_id}
                                             name   = {key.name}
                                             id     = {key.problem_id} />
                            );
                        })}
                    </select>
                </div>
            </div>
        );
    }
});

var LandlordDrop = React.createClass({
    handleTypes: function(e) {
        this.props.selectedVal(e.target.value);
    },
    handleLandlords: function(e) {
        this.props.selectedLL(e.target.value);
    },
    render: function() {
        var landlordsTypes = this.props.landlordsTypes;   
        var landlords = this.props.landlords;
        var conditionTypes = this.props.conditions;
      
        return (
            <div>
                <div className="col-md-3">
                    <select className="form-control" onChange={this.handleTypes}>
                        {<ProblemOptGroup name = {"Landlord-Tenant"}
                                          types = {landlordsTypes} />
                        }

                        {<ProblemOptGroup name = {"Conditions"}
                                          types = {conditionTypes} />
                        }
                    </select>
                </div>

                <div className="col-md-3">
                    <select className="form-control" onChange={this.handleLandlords}>
                        {landlords.map(function (key) {          
                            return (
                                <ProblemList key    = {key.id}
                                             name   = {key.name}
                                             id     = {key.id} />
                            );
                        })}
                    </select>
                </div>
            </div>
        );
    }
});

var ProblemOptGroup = React.createClass({
  render: function() {
    return(
        <optgroup value={this.props.name} label={this.props.name}>
            {this.props.types.map(function (type) {          
                return (
                    <ProblemList key    ={type.problem_id}
                                 name   ={type.name}
                                 id     ={type.problem_id} />
                );
            })}
        </optgroup>
    )
  }
});

var ProblemList = React.createClass({
  render: function() {  
    var list = '';

    if (this.props.referralString != null)
    {
        if (this.props.name == this.props.referralString)
        {
            list = <option value={this.props.id} selected>{this.props.name}</option> 
        }
        else
        {
            list = <option value={this.props.id}>{this.props.name}</option> 
        }
          
    }
    else
    {
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
