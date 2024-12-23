app.service("PAWSProjectService", function ($http) {

    this.addPet = function (formData) {
        return $http.post('/Admin/AddPet', formData, {
            transformRequest: angular.identity,
            headers: { 'Content-Type': undefined }
        });
    };

    this.getPets = function () {
        return $http.get('/Admin/GetPets');
    };

    this.searchPets = function (query) {
        return $http.get('/Admin/SearchPets', { params: { query: query } });
    };

    this.deletePet = function (petID) {
        return $http.post('/Admin/DeletePet', { petID: petID });
    };

    this.editPet = function (formData) {
        return $http.post('/Admin/EditPet', formData, {
            transformRequest: angular.identity,
            headers: { 'Content-Type': undefined }
        });
    };

    this.submitAdoptionForm = function (adoptionData) {
        return $http.post('/Home/SubmitAdoptionForm', adoptionData);
    };

    this.getAdoptionForms = function () {
        return $http.get("/Admin/GetAdoptionForms");
    };

    this.deleteAdoptionForm = function (formID) {
        return $http.post("/Admin/DeleteAdoptionForm", { formID: formID });
    };

    this.updatePetStatus = function (petID, status) {
        return $http.post("/Admin/UpdatePetStatus", { petID: petID, status: status });
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
