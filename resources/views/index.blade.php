<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        #table_data,
        #form_data {
            padding: 15px;
        }

        table {
            font-family: arial, sans-serif;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>

<body>
    <div style="padding:0px 15px">
        <a href="javascript:void(0);" id="addData" style="margin-right:10px">Add User</a>
        <a href="javascript:void(0);" id="blukDelete">Bluk Delete</a>
    </div>
    <div id="table_data" style="display:show">
    </div>
    <div id="form_data" style="display:none">
        <form id="form" method="POST">
            @csrf
            <div>
                <label for="name">Name:</label><br>
                <input type="text" id="name" name="name">
                <br>
                <span style="color:red;" class="error_span" id="error_name"></span>
            </div>
            <br>
            <div>
                <label for="contact_no">Contact No:</label><br>
                <input type="text" id="contact_no" name="contact_no"><br>
                <span style="color:red;" class="error_span" id="error_contact_no"></span>
            </div>
            <br>
            <div>
                <label for="hobbies">Hobbies:</label><br>
                @foreach ($hobbies as $key => $value)
                <input type="checkbox" id="hobby_{{ $key }}" value="{{ $key }}" name="hobby[]">
                <label for="hobby_{{ $key }}">{{ $value }}</label>
                <br>
                @endforeach
                <span style="color:red;" class="error_span" id="error_hobby"></span>
            </div>
            <br>
            <div>
                <label for="category_id">Category:</label><br>
                <select name="category_id" id="category_id">
                <option value=""> Select Category </option>
                    @foreach ($categories as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                <br>
                <span style="color:red;" class="error_span" id="error_category_id"></span>
            </div>
            <br>

            <div>
                <label for="profile_pic">Profile Pic:</label><br>
                <input type="file" id="profile_pic" name="profile_pic"><br>
                <span style="color:red;" class="error_span" id="error_profile_pic"></span>
            </div>
            <br>
            <input type="submit" value="Submit">
            <input type="button" value="Cancel" id="formClose">
        </form>
    </div>
    <script src="{{ asset('storage\assets\js\jquery-3.7.1.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            /* csrf token generate  */
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        });
        var basePath = "{{ url('/') }}";
        $(document).ready(function() {
            getUsers();
        });

        function getUsers() {
            $.ajax({
                type: 'GET',
                url: basePath + "/users",
                dataType: 'json',
            }).done(function(data) {
                $('#table_data').html(data.html);
            }).fail(function() {
                alert('user could not be loaded.');
            });
        }
        $(document).on('click', '#addData', function(e) {
            $('#form')[0].reset();
            $("#table_data").hide()
            $("#form_data").show()
        });

        $(document).on('click', '#blukDelete', function(e) {
            var selectedIds = [];
            $(".bulk-checkbox:checked").each(function() {
                selectedIds.push($(this).val());
            });
            if (selectedIds.length > 0) {
                const confirmBox = confirm("Are you sure? You won't be able to revert this!");
                if (confirmBox) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _method: 'DELETE',
                            ids: selectedIds,
                        },
                        url: "{{ route('users.bulk-delete') }}",
                    }).done(function(data) {
                        alert(data.message);
                        getUsers();
                    }).fail(function(error) {
                        alert(error.responseJSON.message);
                    });
                }
            } else {
                alert("No records selected for deletion.");
            }
        });


        $(document).on('click', '#formClose', function(e) {
            $("#table_data").show()
            $("#form_data").hide()
        });

        $(document).on('click', '#cancelEdit', function(e) {
            getUsers();
        });


        /** form submit */
        $(document).on('submit', '#form', function(e) {
            e.preventDefault();
            let formData = new FormData($('#form')[0]);
            storeData('add', formData);
        })

        function storeData(type, formData) {
            errorHide();
            $.ajax({
                type: 'POST',
                url: "{{ route('users.store') }}",
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
            }).done(function(data) {
                errorHide();
                $('#form')[0].reset();
                alert(data.message);
                getUsers();
                $("#table_data").show()
                $("#form_data").hide()
            }).fail(function(errors) {
                errorShow(errors,type); 
            });
        }
        $(document).on('click', '.updateData', function(e) {
            e.preventDefault();
            let id = $(this).data('id')
            var editFormData = new FormData();
           
            $(this).closest('tr').find('.editable').each(function() {
                const $field = $(this);
                const field = $field.data('field');
                const value = $field.find('input, select').val();
                editFormData[field] = value;
                if(field != 'hobbies' && field != 'profile_pic' && field != 'action'){
                    editFormData.append(field, value)
                }
            });
            $(".edit_hobby:checked").each(function() {
                editFormData.append('hobby[]', $(this).val())
            });
            if($('#edit_profile_pic')[0].files[0]){
                editFormData.append('profile_pic', $('#edit_profile_pic')[0].files[0]); 
            }
            editFormData.append('id', id)
            storeData('update',editFormData);
        })

        $(document).on('click', '.editData', function(e) {
            if ($('.updateData').length > 0) {
                alert('Please update/cancel previous record.')
            }
            let id = $(this).data('id')
            let $row = $(this).closest('tr');
            let url = "{{ route('users.edit', ':id') }}";
            url = url.replace(':id', id);
            $.ajax({
                dataType: 'json',
                data: {
                    id: id,
                    _method: 'GET'
                },
                url: url,
                name: 'edit_user',
            }).done(function(data) {
                $row.find('.editable').each(function() {
                    const $field = $(this);
                    const field = $field.data('field');
                    const value = data.data[field]; 
                    switch (field) {
                        case 'hobbies':
                            const hobbies = @json($hobbies);
                            const hobbyIds = value.map(hobby => hobby.id);
                            const hobbyCheckboxes = Object.keys(hobbies).map(id => `
                            <input type="checkbox" class="edit_hobby" id="hobby_${id}" value="${id}" name="edit_hobby[]"
                                ${hobbyIds.includes(parseInt(id)) ? 'checked' : ''}>
                            <label for="hobby_${id}">${hobbies[id]}</label><br>
                        `).join('');
                            $field.html(`${hobbyCheckboxes}
                            <span style="color:red;" class="error_span" id="edit_error_hobby"></span>
                            `);
                            break;
                        case 'category_id':
                            const categoryData = @json($categories);
                            const categoryOptions = Object.keys(categoryData).map(id => {
                                const name = categoryData[id]; 
                                return `
                                    <option value="${id}" ${id == value ? 'selected' : ''}>
                                        ${name}
                                    </option>
                                `;
                            }).join('');
                            $field.html(`
                                <select name="edit_category_id" id="category_id">
                                <option value="">
                                        Select Category
                                    </option>
                                    ${categoryOptions}
                                </select>
                                <br>
                                <span style="color:red;" class="error_span" id="edit_error_category_id"></span>
                            `);
                            break;
                        case 'profile_pic':
                            $field.html(`<input type="file" id="edit_${field}" name="edit_${field}" value="${value}">
                            <br>
                            <span style="color:red;" class="error_span" id="edit_error_profile_pic"></span>
                            `);
                            break;
                        case 'action':
                            $field.html(`
                            <a href="javascript:void(0);" class="updateData" data-id="${data.data.id}" >Update</a>
                            <a href="javascript:void(0);" id="cancelEdit" >Cancel</a>
                            `);
                            break;
                        default:
                            $field.html(`<input type="text" name="edit_${field}" value="${value}">
                            <br>
                            <span style="color:red;" class="error_span" id="edit_error_${field}"></span>

                            `);
                            break;
                    }
                });

            }).fail(function(error) {
                alert(error.responseJSON.message);
            });

        });
        $(document).on('click', '.deleteData', function(e) {
            const confirmBox = confirm("Are you sure? You won't be able to revert this!");
            if (confirmBox) {
                let id = $(this).data('id')
                var url = "{{ route('users.destroy', ':id') }}";
                url = url.replace(':id', id);
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _method: 'DELETE'
                    },
                    url: url,
                }).done(function(data) {
                    alert(data.message);
                    getUsers();
                }).fail(function(error) {
                    alert(error.responseJSON.message);
                });
            }
        });

        function errorShow(errors,type) {
            const errorId = type=='add' ? 'error_':'edit_error_';
            $.each(errors.responseJSON.errors, function(field_name, error) {
                let result = field_name.replaceAll(".", "_");
            console.log('#'+errorId + result);
                $('#'+errorId + result).text(error);
            })
        }

        function errorHide() {
            $('.error_span').text('');
        }
    </script>
</body>

</html>