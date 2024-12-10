app.controller("PAWSProjectController", function ($scope, PAWSProjectService) {

    $scope.pet = {};

    $scope.addPet = function () {
        var formData = new FormData();
        formData.append('name', $scope.pet.name);
        formData.append('gender', $scope.pet.gender)
        formData.append('age', $scope.pet.age);
        formData.append('size', $scope.pet.size);
        formData.append('details', $scope.pet.details);
        formData.append('image', $scope.pet.image);

        PAWSProjectService.addPet(formData).then(function (response) {
            // Handle success
            console.log('Pet added successfully');
        }, function (error) {
            // Handle error
            console.log('Error adding pet');
        });
    };
},);

app.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;

            element.bind('change', function () {
                scope.$apply(function () {
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);