// !!The banner_id variable is important!!

// It's being used as a global variable from the head.js where this file is located
// to determine which banner id is being used so it can grab the client data.

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
            addedData: null //Used for storing the data when saving for notifications
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
                console.log(data);   
                this.setState({clientData: data});             
            }.bind(this),
            error: function(xhr, status, err) {
                //var error = "Banner ID doesn't exist."
                window.location = "index.php?module=slc";
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

                console.log(data);
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
        console.log(issueData);
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
                               addedData: issueData});
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
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab referral data.")
                console.error(this.props.url, status, err.toString());
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
                    <ViewVisits key = {data.id}
                    id = {data.id}
                    init_date = {data.initial_date}
                    issues = {data.issues} 
                    getClient = {getClient}
                    newIssue = {newIssue}
                    client = {client} />
                );
            });

            console.log(this.state.referralData);
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
                               addedData = {this.state.addedData} />

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
console.log(this.props.addedData);
        var notification;
        if (this.props.msg != '')
        {
            if (this.props.msgType == 'success')
            {
                if (this.props.addedData != null)
                {
                    notification = <div className="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" className="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <strong>Successfully Added: </strong> 
                                    <br />
                                    {this.props.msg}
                                    <ul>
                                        {this.props.addedData.map(function (key) {          
                                            return (
                                                <ListIssues name   = {key.name}
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
                    notification = <div className="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" className="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <strong>Success!</strong> 
                                    <br />
                                    {this.props.msg}
                                </div>
                }
            }
            else if (this.props.msgType == 'error')
            {
                console.log(this.props.msg);
                notification = <div className="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" className="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
            console.log(this.props.llID)
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
        if (r_id != -1)
        {
            this.props.postReferral(r_id);
        }
    },
    render: function(){
        var referralData = this.props.referralData;
        var referralString = this.props.referralString;
        return (
            <div className="row">
                <div className="col-md-3">
                    <form className="form-horizontal" role="form">
                        Referral:
                        <select className="form-control" onChange={this.handleReferral}>
                            {referralData.map(function (key) {          
                                return (
                                    <ProblemList key={key.referral_id}
                                        name={key.name}
                                        id={key.referral_id}
                                        referralString={referralString} />
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
        var link = 'index.php?module=slc&view=NewIssue&visitid='+this.props.id+"&cname="+this.props.client.name;

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
                        <span style={{"fontSize": 18, "fontWeight":'bold'}}>{this.props.init_date}</span>
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

// !!This uses ReactBootstrap!!
var ModalForm = React.createClass({
    getInitialState: function() {
        return {
            issueData: [],
            type: '',
        };
    },
    handleSave: function(){
        // Used so that the new visit modal doesn't close if nothings selected.
        if (this.state.issueData.length !== 0)
        {
            console.log(this.state.issueData)
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
        this.setState({type: 'LandlordTenant'});
    },
    handleCriminal: function(){
        this.setState({type: 'Criminal'});
    },
    handleOther: function(){
        this.setState({type: 'Other'});
    },
    displaySelectedIssues: function(issues){
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
                                       displaySelectedIssues = {this.displaySelectedIssues}
                                       searchObjectIndexOf = {this.searchObjectIndexOf} 
                                       findProblemName = {this.findProblemName}
                                       findLandlordName = {this.findLandlordName} />;
            }
            else if (this.state.type == 'Other')
            {
                dData = <ProblemTypeDrop  type      = {types.Other}
                                          issueData = {this.state.issueData} 
                                          displaySelectedIssues = {this.displaySelectedIssues}
                                          searchIndexOf = {this.searchIndexOf}
                                          findProblemName = {this.findProblemName} />;
            }
            else
            {
                dData = <ProblemTypeDrop  type      = {types.Criminal}  
                                          issueData = {this.state.issueData}
                                          displaySelectedIssues = {this.displaySelectedIssues}
                                          searchIndexOf = {this.searchIndexOf}
                                          findProblemName = {this.findProblemName} />;
            }   
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
                                <PrintIssues name   = {key.name}
                                             llID   = {key.llID} 
                                             id     = {key.id}
                                             llName = {key.llName}
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
    getInitialState: function() {
        return {
            selectedVal: null
        };
    },  
    handleTypes: function(e) {
        this.setState({selectedVal: e.target.value});
    },
    handleAdd: function() {
        var type = this.state.selectedVal;

        if (type != -1 && type != null)
        {
            var items = this.props.issueData;
            if (this.props.searchIndexOf(type) == -1)
            {
                items.push({id:type,
                            name:this.props.findProblemName(type)});
                console.log(items);
                this.props.displaySelectedIssues(items);
            }
        }
    },
    render: function() {

        return (
            <div>
                <div className="col-md-3">
                    <select className="form-control" onChange={this.handleTypes}>
                        {this.props.type.map(function (key) {          
                            return (
                                <ProblemList key={key.problem_id}
                                    name={key.name}
                                    id={key.problem_id} />
                            );
                        })}
                    </select>
                </div>

                <div className="col-md-3">
                    <button className="btn btn-default" type="submit" onClick={this.handleAdd}>Add</button>
                </div>

            </div>
        );
    }
});

var LandlordDrop = React.createClass({
    getInitialState: function() {
        return {
            selectedType: -1,
            selectedLl: -1
        };
    },  
    handleTypes: function(e) {
        this.setState({selectedType: e.target.value});
    },
    handleLandlords: function(e) {
        this.setState({selectedLl: e.target.value});
    },
    handleAdd: function() {
        //grab value and place in array.
        var landlord = this.state.selectedLl;
        var type = this.state.selectedType;

        if (type != -1 && landlord != -1)
        {
            var items = this.props.issueData;
            if (this.props.searchObjectIndexOf(type, landlord) == -1)
            {    
                items.push({id:type, 
                            name:this.props.findProblemName(type),
                            llID:landlord,
                            llName:this.props.findLandlordName(landlord)});
                this.props.displaySelectedIssues(items);
                console.log(items);
            }
        }
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
                                <ProblemList key={key.id}
                                    name={key.name}
                                    id={key.id} />
                            );
                        })}
                    </select>
                </div>

                <div className="col-md-3">
                    <button className="btn btn-default" type="submit" onClick={this.handleAdd} >Add</button>
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
                    <ProblemList key={type.problem_id}
                        name={type.name}
                        id={type.problem_id} />
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
