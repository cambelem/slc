/**
              IMPORTANT!
   ******************************
   *  The following component   *
   *    uses ReactBootstrap     *
   ******************************
**/                               
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
        // prevents the modal from closing if the 
        // user clicks the save button with nothing
        // selected.
        if (this.state.issueData.length !== 0)
        {
            this.props.newVisit(this.state.issueData);
            this.props.sendEmail();
            this.props.onRequestHide();
        }
    },
    removeItem: function(id){
        // Finds the issue the user wishes to delete and removes it.
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
    searchIndexOf: function(searchTerm){
        // Used by Criminal and Other, checks the given
        // id value from the dropbox and if the value is not present,
        // returns a -1.
        for(var i = 0; i < this.state.issueData.length; i++)
        {
            if(this.state.issueData[i]['id'] === searchTerm)
                return i;
        }
        return -1;
    },
    searchObjectIndexOf: function(type, landlord){
        var isTypeThere = false;
        // Used by LandlordTenant, checks the given
        // id/ll_id value from the dropbox and if the value is not present,
        // returns a -1.
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
    findProblemName: function(pid){
        // Determines the problem name based off of the pid.
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
        // Determines the landlord name based off of the llid.
        var landlords = this.props.issueTreeData.landlords;
        for (var landlord in landlords)
        {
            if (llid == landlords[landlord].id)
            {
                return landlords[landlord].name;
            }
        }
    },
    handleAdd: function() { 
        // Used by Criminal/Other
        var type = this.state.selectedVal;
        var landlord = this.state.selectedLL;
        var items = this.state.issueData;

        // Adds the values given from dropdown boxes
        if (type != -1 && landlord == -1)
        {
            if (this.searchIndexOf(type) == -1)
            {
                // Adds the item to the array.
                items.push({id:type,
                            name:this.findProblemName(type)});
                this.setState({issueData: items});
            }
        }

    },
    handleLandlordAdd: function() {
        // Used by LandlordTenant
        var type = this.state.selectedVal;
        var landlord = this.state.selectedLL;
        var items = this.state.issueData;

        // Adds the values given from dropdown boxes
        if (type != -1 && landlord != -1)
        {
            if (this.searchObjectIndexOf(type, landlord) == -1)
            {    
                // Adds the item to the array.
                items.push({id:type, 
                            name:this.findProblemName(type),
                            llID:landlord,
                            llName:this.findLandlordName(landlord)});
                this.setState({issueData: items});
            }
        }
    },
    selectedVal: function(val) {
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

        // Creates a drop box/add box based on the button pressed
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
                                                removeItem = {removeItem} />        
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

/**
    Component that helps to show selected issues in the modal form.
**/
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


/**
    Component that enables the printing of issues within a modal form.
    Also adds the ability to remove the issue.
**/
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


/**
    Component that aides the Criminal and Other dropdown box creation.
**/
var ProblemTypeDrop = React.createClass({
    handleTypes: function(e) {
        this.props.selectedVal(e.target.value);
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

/**
    Component that aides the LandlordTenant dropdown box creation.
**/
var LandlordDrop = React.createClass({
    handleTypes: function(e) {
        // Grabs issue id
        this.props.selectedVal(e.target.value);
    },
    handleLandlords: function(e) {
        // Grabs landlord id
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

/**
    Helper component for LandlordDrop, aids in the creation of
    optgroups for Landlord/condition groups.
**/
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
