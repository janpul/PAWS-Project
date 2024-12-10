app.service("PAWSProjectService", function ($http) {

    this.addPet = function (formData) {
        return $http.post('/Admin/AddPet', formData, {
            transformRequest: angular.identity,
            headers: { 'Content-Type': undefined }
        });
    };
});