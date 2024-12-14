app.controller("PAWSProjectController", function ($scope, PAWSProjectService) {

    $scope.pet = {};

    $scope.addPet = function () {
        var formData = new FormData();
        formData.append('name', $scope.pet.name);
        formData.append('gender', $scope.pet.gender);
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
});

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

app.controller("UserController", function ($scope, $timeout, UserService) {
    $scope.users = [];
    $scope.user = {};
    $scope.isEdit = false;
    $scope.originalUsername = '';
    $scope.searchQuery = null;
    $scope.toastMessage = '';
    $scope.toastType = '';
    $scope.usernameTaken = false;

    $scope.checkUsername = function () {
        if ($scope.user.username) {
            UserService.usernameTaken($scope.user.username).then(function (response) {
                $scope.usernameTaken = response.data;
            });
        }
    };

    // Fetch users when the page is loaded
    $scope.searchUsers = function () {
        UserService.searchUsers($scope.searchQuery).then(function (response) {
            $scope.users = response.data;
        });
    };

    // Call searchUsers on page load
    $scope.searchUsers();  // This will load all users initially

    $scope.editUser = function (user) {
        $scope.isEdit = true;
        $scope.user = angular.copy(user);
        $scope.originalUsername = user.username;
        $scope.user.isActive = user.isActive.toString(); // Convert integer to string for dropdown
        $scope.user.password = '';
    };

    $scope.cancelEdit = function () {
        $scope.isEdit = false;
        $scope.user = {};
        $scope.originalUsername = '';
    };

    $scope.saveUser = function () {
        // Ensure isActive is an integer
        $scope.user.isActive = parseInt($scope.user.isActive, 10);

        if ($scope.usernameTaken) {
            $scope.showToast('Username is already taken', 'error');
            return;
        }

        if ($scope.isEdit) {
            UserService.updateUser($scope.user).then(function (response) {
                $scope.searchUsers();
                $scope.resetForm();
                $scope.showToast('User updated successfully', 'success');
            }, function (error) {
                $scope.showToast('Failed to update user', 'error');
            });
        } else {
            UserService.createUser($scope.user).then(function (response) {
                $scope.searchUsers();
                $scope.resetForm();
                $scope.showToast('User created successfully', 'success');
            }, function (error) {
                $scope.showToast('Failed to create user', 'error');
            });
        }
    };

    $scope.confirmDelete = function (userID) {
        if (confirm("Are you sure you want to delete this user?")) {
            $scope.deleteUser(userID);
        }
    };

    $scope.deleteUser = function (userID) {
        UserService.deleteUser(userID).then(function (response) {
            $scope.searchUsers();
            $scope.showToast('User deleted successfully', 'success');
        }, function (error) {
            $scope.showToast('Failed to delete user', 'error');
        });
    };


    $scope.resetForm = function () {
        $scope.user = {};
        $scope.isEdit = false;
        $scope.originalUsername = '';
    };

    $scope.showToast = function (message, type) {
        $scope.toastMessage = message;
        $scope.toastType = type;
        $('.toast').toast('show');
        $timeout(function () {
            $scope.hideToast();
        }, 3000); // Toast disappears after 3 seconds
    };

    $scope.hideToast = function () {
        $scope.toastMessage = '';
        $('.toast').toast('hide');
    };
});



