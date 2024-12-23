app.controller("PAWSProjectController", function ($scope, $timeout, $window, PAWSProjectService) {
    $scope.pet = {};

    $scope.addPet = function () {
        var formData = new FormData();
        formData.append('name', $scope.pet.name);
        formData.append('gender', $scope.pet.gender);
        formData.append('age', $scope.pet.age);
        formData.append('size', $scope.pet.size);
        formData.append('details', $scope.pet.details);
        formData.append('image', $scope.pet.image);
        formData.append('addedBy', $scope.username); // Add the username to the form data

        PAWSProjectService.addPet(formData).then(function (response) {
            // Handle success
            Swal.fire({
                title: 'Success!',
                text: 'Pet added successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    $timeout(function () {
                        $window.location.href = '/Admin/Manage';
                    }, 2000); // Redirect after 2 seconds
                }
            });
            console.log('Pet added successfully');
            $scope.resetForm();
        }, function (error) {
            // Handle error
            Swal.fire({
                title: 'Error!',
                text: 'Error adding pet',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            console.log('Error adding pet');
        });
    };

    $scope.resetForm = function () {
        $scope.pet = {};
        if ($scope.petForm) {
            $scope.petForm.$setPristine();
            $scope.petForm.$setUntouched();
        }
    };

    $scope.pets = [];
    $scope.searchQuery = "";
    $scope.isEdit = false;
    $scope.pet = {};

    $scope.getPets = function () {
        PAWSProjectService.getPets().then(function (response) {
            $scope.pets = response.data;
        });
    };

    $scope.searchPets = function () {
        PAWSProjectService.searchPets($scope.searchQuery).then(function (response) {
            $scope.pets = response.data;
        });
    };

    $scope.editPet = function (pet) {
        $scope.isEdit = true;
        $scope.pet = angular.copy(pet);
        $scope.originalPetName = pet.name; // Store the original pet name
        $scope.resetFileInput = false;
    };

    $scope.savePet = function () {
        if ($scope.isEdit) {
            var formData = new FormData();
            formData.append('petID', $scope.pet.petID);
            formData.append('name', $scope.pet.name);
            formData.append('gender', $scope.pet.gender);
            formData.append('age', $scope.pet.age);
            formData.append('size', $scope.pet.size);
            formData.append('details', $scope.pet.details);
            formData.append('status', $scope.pet.status);
            if ($scope.pet.image) {
                formData.append('image', $scope.pet.image);
            }

            PAWSProjectService.editPet(formData).then(function (response) {
                $scope.getPets();
                $scope.isEdit = false;
                $scope.pet = {};
                $scope.originalPetName = ''; // Clear the original pet name
                $scope.resetFileInput = true; // Reset the file input field

                // Show success alert
                Swal.fire({
                    title: 'Success!',
                    text: 'Pet edited successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }, function (error) {
                // Handle error
                Swal.fire({
                    title: 'Error!',
                    text: 'Error editing pet',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    };


    $scope.deletePet = function (petID) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                PAWSProjectService.deletePet(petID).then(function (response) {
                    $scope.getPets();
                    Swal.fire(
                        'Deleted!',
                        'Pet has been deleted.',
                        'success'
                    );
                });
            }
        });
    };

    $scope.cancelEdit = function () {
        $scope.isEdit = false;
        $scope.pet = {};
        $scope.originalPetName = ''; // Clear the original pet name
        $scope.resetFileInput = true; // Reset the file input field
    };

    $scope.getPets();

    $scope.adoptionForm = {};

    $scope.submitAdoptionForm = function () {
        PAWSProjectService.submitAdoptionForm($scope.adoptionForm).then(function (response) {
            if (response.data.success) {
                // Handle success
                Swal.fire({
                    title: 'Success!',
                    text: 'Adoption form submitted successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.closeModal(); // Close the modal
                    }
                });
                console.log('Adoption form submitted successfully');
                $scope.resetAdoptionForm();
            } else {
                // Handle error
                Swal.fire({
                    title: 'Error!',
                    text: 'Error submitting adoption form: ' + response.data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                console.log('Error submitting adoption form');
            }
        }, function (error) {
            // Handle error
            Swal.fire({
                title: 'Error!',
                text: 'Error submitting adoption form',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            console.log('Error submitting adoption form');
        });
    };


    $scope.resetAdoptionForm = function () {
        $scope.adoptionForm = {};
        if ($scope.adoptionFormElement) {
            $scope.adoptionFormElement.$setPristine();
            $scope.adoptionFormElement.$setUntouched();
        }
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

app.directive('resetFileInput', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            scope.$watch(attrs.resetFileInput, function (value) {
                if (value) {
                    element.val(null);
                }
            });
        }
    };
});

app.controller("UserController", function ($scope, $timeout, UserService) {
    $scope.users = [];
    $scope.user = {};
    $scope.isEdit = false;
    $scope.originalUsername = '';
    $scope.searchQuery = null;
    $scope.usernameTaken = false;

    $scope.checkUsername = function () {
        if ($scope.user.username && $scope.user.username !== $scope.originalUsername) {
            UserService.usernameTaken($scope.user.username).then(function (response) {
                $scope.usernameTaken = response.data;
            });
        } else {
            $scope.usernameTaken = false;
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

        if (!$scope.isEdit && $scope.usernameTaken) {
            Swal.fire({
                title: 'Error!',
                text: 'Username is already taken',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if ($scope.isEdit) {
            UserService.updateUser($scope.user).then(function (response) {
                $scope.searchUsers();
                $scope.resetForm();
                Swal.fire({
                    title: 'Success!',
                    text: 'User updated successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }, function (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to update user',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        } else {
            UserService.createUser($scope.user).then(function (response) {
                $scope.searchUsers();
                $scope.resetForm();
                Swal.fire({
                    title: 'Success!',
                    text: 'User created successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }, function (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to create user',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    };

    $scope.confirmDelete = function (userID) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $scope.deleteUser(userID);
            }
        });
    };

    $scope.deleteUser = function (userID) {
        UserService.deleteUser(userID).then(function (response) {
            $scope.searchUsers();
            Swal.fire({
                title: 'Deleted!',
                text: 'User deleted successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }, function (error) {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to delete user',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    };

    $scope.resetForm = function () {
        $scope.user = {};
        $scope.isEdit = false;
        $scope.originalUsername = '';
    };
});
