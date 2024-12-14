app.service("PAWSProjectService", function ($http) {

    this.addPet = function (formData) {
        return $http.post('/Admin/AddPet', formData, {
            transformRequest: angular.identity,
            headers: { 'Content-Type': undefined }
        });
    };
});

app.service("UserService", function ($http) {
    this.searchUsers = function (query) {
        return $http.get('/Admin/SearchUsers', { params: { search: query } });
    };

    this.createUser = function (user) {
        return $http.post('/Admin/CreateUser', user);
    };

    this.updateUser = function (user) {
        return $http.put('/Admin/UpdateUser', user);
    };

    this.deleteUser = function (userID) {
        return $http.delete('/Admin/DeleteUser', { params: { id: userID } });
    };

    this.usernameTaken = function (username) {
        return $http.get('/Admin/UsernameCheck', { params: { username: username } });
    };
});
