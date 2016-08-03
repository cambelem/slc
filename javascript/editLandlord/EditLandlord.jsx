var ReactCSSTransitionGroup = React.addons.CSSTransitionGroup;

var ViewEditLandlords = React.createClass({
    getInitialState: function() {
        return {
            landlordData: null,
            filteredData: null,
            errorMessage: null,
            errorType: null
        };
    },
    componentWillMount: function(){
        this.getLandlordData();
    },
    getLandlordData: function(){
        $.ajax({
            url: 'index.php?module=slc&action=GETLandlordData',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                this.setState({landlordData: data.landlords});
                if(this.state.filteredData != null){
                    this.refs.search.updateData(this.state.landlordData);
                } else {
                    this.setState({filteredData: data.landlords});
                }
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab landlord data. "+err.toString());
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },
    addLandlord: function(llData){
        if(this.validate(llData))
        {
            var landlord = JSON.stringify(llData);

            $.ajax({
                url: 'index.php?module=slc&action=POSTLandlord',
                type: 'POST',
                data: landlord,
                dataType: 'json',
                success: function(data) {
                    this.getLandlordData();
                    this.setState({errorType: data['errorType'],
                                   errorMessage: data['msg']});
                }.bind(this),
                error: function(xhr, status, err) {
                    var message = "Could not add landlord: " + llData;
                    this.setState({errorType: "warning",
                                   errorMessage: message});
                    console.error(this.props.url, status, err.toString());
                }.bind(this)
            });
        } else {
            var message = "Could not add landlord. Please ensure the landlord is not a space or empty.";
            this.setState({errorType: "warning",
                           errorMessage: message});
        }
    },
    editLandlord: function(llData){
        if(this.validate(llData))
        {
            var landlord = JSON.stringify(llData);

            $.ajax({
                url: 'index.php?module=slc&action=PUTLandlord',
                type: 'PUT',
                data: landlord,
                dataType: 'json',
                success: function(data) {
                    this.getLandlordData();
                    this.setState({errorType: data['errorType'],
                                   errorMessage: data['msg']});
                }.bind(this),
                error: function(xhr, status, err) {
                    var message = "Did not successfully edit landlord: " + llData.name;
                    this.setState({errorType: "warning",
                                   errorMessage: message});
                    console.error(this.props.url, status, err.toString());
                }.bind(this)
            });
        } else {
            var message = "Could not edit landlord. Please ensure the landlord is not empty.";
            this.setState({errorType: "warning",
                           errorMessage: message});
        }
    },
    setDisplay: function(data) {
        this.setState({filteredData: data});
    },
    validate: function(data) {
        var name;
        if(data.name != undefined){
            name = data.name;
        } else {
            name = data;
        }

        
        if(name.trim() == '' || name == undefined || name == null){
            return false;
        } else {
            return true;
        }
    },
    render: function() {
        var errors;
        if(this.state.errorType == null){
            errors = '';
        } else {
            errors = <ErrorMessagesBlock key="errorSet" message = {this.state.errorMessage} errorType = {this.state.errorType} />
        }

        if(this.state.landlordData != null && this.state.filteredData != null){
            return (
                <div>
                    <div className="row">
                        <div className="col-md-6">
                        <h1><i className="fa fa-pencil-square-o" aria-hidden="true"></i> Edit Landlords</h1><br/>
                        </div>

                        <div className="col-md-12">
                            <ReactCSSTransitionGroup transitionName="example" transitionEnterTimeout={500} transitionLeaveTime={500}>
                                {errors}
                            </ReactCSSTransitionGroup>
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-md-6">
                            <div className="row">
                                <div className="col-md-6">
                                    <div className="form-group">
                                        <label>Search:</label>
                                        <Search landlordData = {this.state.landlordData}
                                                setDisplay   = {this.setDisplay}
                                                ref          = "search" />
                                    </div>
                                </div>
                                <br />
                                <div className="col-md-9">
                                <Table landlordData = {this.state.filteredData} 
                                       editLandlord = {this.editLandlord} />
                                </div>
                            </div>
                        </div>

                        <div className="col-md-6">
                            <form>
                            <AddLandlords addLandlord = {this.addLandlord} />
                            </form>
                        </div>
                    </div>
                </div>
            );
        }else{
            return( <p className="text-muted" style={{position:"absolute", top:"50%", left:"50%"}}>
                        <i className="fa fa-spinner fa-2x fa-spin"></i> Loading Landlord Data...
                    </p>
            );
        }
    }
});

var Search = React.createClass({
    getInitialState: function() {
        return{
            searchPhrase: '',
            data: null
        };
    },
    componentWillMount: function() {
        this.setState({data: this.props.landlordData})
    },
    updateData: function(newData) {
        this.setState({data:newData}, function(){
            this.searchList();
        });
    },
    searchList: function(e)
    {
        var data = this.state.data;

        try {
            // Saves the phrase that the user is looking for.
            var phrase = e.target.value.toLowerCase();
            this.setState({searchPhrase: phrase});
        }
        catch (err)
        {
            var phrase = this.state.searchPhrase;
        }

        var filtered = [];

        // Looks for the phrase by filtering the mainData
        for (var i = 0; i < data.length; i++) {
            var item = data[i];

            if (item.name.toLowerCase().includes(phrase))
            {
                filtered.push(item);
            }
        }
        this.props.setDisplay(filtered);
    },
    render:function(){
        return(
            <input type="text" className="form-control" ref="searchBox" onChange={this.searchList}/>
        );
    }
});

var Table = React.createClass({
    render: function(){
        var editLandlord = this.props.editLandlord;
        return(
            <table className="table table-condensed table-striped">
                <thead>
                    <tr>
                        <th>Fullname</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    {this.props.landlordData.map(function (landlord) {
                        return (
                            <LandlordRow key    = {landlord.id}
                                         id     = {landlord.id}
                                         name   = {landlord.name}
                                         editLandlord = {editLandlord} />
                            );
                        })}
                </tbody>
            </table>
        );
    }
});

var LandlordRow = React.createClass({
    getInitialState: function() {
        return {
            showEdit: false,
        };
    },
    handleEdit: function() {
        this.setState({showEdit: true});
    },
    handleSave: function() {
        this.setState({showEdit: false});
        var landlord = ({id: this.props.id,
                         name: this.refs.llName.value});
        this.props.editLandlord(landlord);
    },
    render: function() {
        if (!this.state.showEdit){
            return( 
                <tr>
                    <td>{this.props.name}</td>
                    <td> <a onClick={this.handleEdit}> <i className="fa fa-pencil-square-o" /> </a> </td>
                </tr>
            );
        } else {
            return( 
                <tr>
                    <td> <input type="text" className="form-control" ref="llName" defaultValue={this.props.name}/> </td>
                    <td> <button className="btn btn-default btn-sm" onClick={this.handleSave}>Save</button> </td>
                </tr>
            );
        }
    }
    
});

var AddLandlords = React.createClass({
    handleSubmit: function(e) {
        e.preventDefault();
        this.props.addLandlord(this.refs.landlord.value);
    },
    render: function() {
        return( 
            <div className="panel panel-default">
                <div className="panel-body">
                    <div className="form-group">
                        <div className="col-md-12">
                            
                            <div className="col-md-6">
                            <label>Landlord Name:</label>
                            <input type="text" className="form-control" ref="landlord" />
                            </div>
                            <div className="col-md-6">
                            <br />
                            <button type="submit" className="btn btn-default" onClick={this.handleSubmit}>Create Landlord</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        );
    }
    
});

var ErrorMessagesBlock = React.createClass({
    render: function() {
        if(this.props.errors === null){
            return '';
        }

        var errorType;
        if(this.props.errorType == "success"){
            errorType = <div className="alert alert-success" role="alert">
                            <p><i className="fa fa-exclamation-circle fa-2x"></i> {this.props.message}</p>
                        </div>
        } else if(this.props.errorType == "warning"){
            errorType = <div className="alert alert-danger" role="alert">
                            <p><i className="fa fa-exclamation-circle fa-2x"></i> {this.props.message} </p>
                        </div>
        }

        return (
            <div className="row">
                <div className="col-md-4 col-md-offset-4">  
                    {errorType}
                </div>
            </div>
        );
    }
});


ReactDOM.render(
    <ViewEditLandlords />,
    document.getElementById('editLandlordsView')
);
