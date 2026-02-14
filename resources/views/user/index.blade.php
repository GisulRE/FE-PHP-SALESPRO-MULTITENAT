@extends('layout.main') @section('content')
    @if (session()->has('message1'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message1') !!}
        </div>
    @endif
    @if (session()->has('message2'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message2') }}</div>
    @endif
    @if (session()->has('message3'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message3') }}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif

    <section>
        @if (in_array('users-add', $all_permission))
            <div class="container-fluid">
                <a href="{{ route('user.create') }}" class="btn btn-info"><i class="dripicons-plus"></i>
                    {{ trans('file.Add User') }}</a>
            </div>
        @endif
        <div class="table-responsive">
            <table id="user-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.UserName') }}</th>
                        <th>{{ trans('file.Email') }}</th>
                        <th>{{ trans('file.Company Name') }}</th>
                        <th>{{ trans('file.Phone Number') }}</th>
                        <th>{{ trans('file.Role') }}</th>
                        <th>{{ trans('file.Status') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_user_list as $key => $user)
                        <tr data-id="{{ $user->id }}">
                            <td>{{ $key }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->company_name }}</td>
                            <td>{{ $user->phone }}</td>
                            <?php $role = DB::table('roles')->find($user->role_id); ?>
                            <td>{{ $role->name }}</td>
                            @if ($user->is_active)
                                <td>
                                    <div class="badge badge-success">Active</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-danger">Inactive</div>
                                </td>
                            @endif
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">{{ trans('file.action') }}
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                        user="menu">
                                        <li>
                                            <button type="button" data-id="{{ $user->id }}" class="btn btn-link"
                                                onclick="openDialog('{{ $user->id }}', '{{ $user->name }}')"
                                                data-toggle="modal" data-target="#editModal"><i
                                                    class="dripicons-document-edit"></i>
                                                {{ trans('file.permission category') }}
                                            </button>
                                        </li>
                                        <li class="divider"></li>
                                        @if (in_array('users-edit', $all_permission))
                                            <li>
                                                <a href="{{ route('user.edit', $user->id) }}" class="btn btn-link"><i
                                                        class="dripicons-document-edit"></i> {{ trans('file.edit') }}</a>
                                            </li>
                                        @endif
                                        <li class="divider"></li>
                                        @if (in_array('users-delete', $all_permission))
                                            {{ Form::open(['route' => ['user.destroy', $user->id], 'method' => 'DELETE']) }}
                                            <li>
                                                <button type="submit" class="btn btn-link"
                                                    onclick="return confirmDelete()"><i class="dripicons-trash"></i>
                                                    {{ trans('file.delete') }}</button>
                                            </li>
                                            {{ Form::close() }}
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => ['user.updatePermission'], 'method' => 'put']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title"> {{ trans('file.Update Permissions') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <input type="hidden" name="user_id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ trans('file.User') }} *</label>
                                <input type="text" name="username" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Categorias</label>
                                <select class="selectpicker form-control" name="categories[]" id="categories"
                                    title="Seleccione uno o más..." multiple>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                </div>
                {{ Form::close() }}
            </div>
        </div>


        <script type="text/javascript">
            $("ul#people").siblings('a').attr('aria-expanded', 'true');
            $("ul#people").addClass("show");
            $("ul#people #user-list-menu").addClass("active");

            var user_id = [];
            var all_permission = <?php echo json_encode($all_permission); ?>;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function confirmDelete() {
                if (confirm("Are you sure want to delete?")) {
                    return true;
                }
                return false;
            }

            function openDialog(idp, username) {
                var url = "user/"
                url = url.concat("permission-category/").concat(idp);
                $("#categories").val([]);
                $.get(url, function(data) {
                    $("#editModal input[name='user_id']").val(idp);
                    $("#editModal input[name='username']").val(username);
                    var listCategories = data['categories'];
                    $.each(listCategories, function(i, e) {
                        $("#categories option[value='" + e.category_id + "']").prop("selected", true);
                    });
                    $('.selectpicker').selectpicker('refresh');
                });
            }

            $(document).ready(function() {
                $('.modal').on('hidden.bs.modal', function(e) {
                    $(this).removeData();
                });
            });

            $('#user-table').DataTable({
                "order": [],
                'language': {
                    'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                    "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                    "search": '{{ trans('file.Search') }}',
                    'paginate': {
                        'previous': '<i class="dripicons-chevron-left"></i>',
                        'next': '<i class="dripicons-chevron-right"></i>'
                    }
                },
                'columnDefs': [{
                        "orderable": false,
                        'targets': [0, 7]
                    },
                    {
                        'render': function(data, type, row, meta) {
                            if (type === 'display') {
                                data =
                                    '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                            }

                            return data;
                        },
                        'checkboxes': {
                            'selectRow': true,
                            'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                        },
                        'targets': [0]
                    }
                ],
                'select': {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                'lengthMenu': [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                dom: '<"row"lfB>rtip',
                buttons: [{
                        extend: 'pdf',
                        text: '{{ trans('file.PDF') }}',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                    },
                    {
                        extend: 'csv',
                        text: '{{ trans('file.CSV') }}',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                    },
                    {
                        extend: 'print',
                        text: '{{ trans('file.Print') }}',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible'
                        },
                    },
                    {
                        text: '{{ trans('file.delete') }}',
                        className: 'buttons-delete',
                        action: function(e, dt, node, config) {
                            user_id.length = 0;
                            $(':checkbox:checked').each(function(i) {
                                if (i) {
                                    user_id[i - 1] = $(this).closest('tr').data('id');
                                }
                            });
                            if (user_id.length && confirm("Are you sure want to delete?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'user/deletebyselection',
                                    data: {
                                        userIdArray: user_id
                                    },
                                    success: function(data) {
                                        alert(data);
                                    }
                                });
                                dt.rows({
                                    page: 'current',
                                    selected: true
                                }).remove().draw(false);
                            } else if (!user_id.length)
                                alert('No user is selected!');
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '{{ trans('file.Column visibility') }}',
                        columns: ':gt(0)'
                    },
                ],
            });

            if (all_permission.indexOf("users-delete") == -1)
                $('.buttons-delete').addClass('d-none');
        </script>
    @endsection
