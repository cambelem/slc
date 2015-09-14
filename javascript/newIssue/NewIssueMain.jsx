
var NewIssueMain = React.createClass({
    getInitialState: function() {
        return {
            clientData: null,
            errorWarning: ''
        };
    },
    componentWillMount: function(){
        this.getClientData();
    },
    getData: function(){
        $.ajax({
            url: //'index.php?module=slc&action=GetStudentClientData&banner_id=' + banner_id,
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
    render: function() {
        
        if (this.state.clientData == null)
        {
            var client = null;
            return (<div></div>);
        }
        else
        {
            var client = this.state.clientData.client;
        }
        return (
            <div>
                
            </div>
        );
    }
});




React.render(
    <NewIssueMain />,
    document.getElementById('clientviews')
);
