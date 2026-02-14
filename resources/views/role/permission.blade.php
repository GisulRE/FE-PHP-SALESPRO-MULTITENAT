@extends('layout.main')
@section('content')
    {{-- Debug temporal --}}
    @php
        if (!isset($blocked_modules)) {
            $blocked_modules = [];
        }
        // Log::info('Vista - Blocked Modules: ' . print_r($blocked_modules, true));
    @endphp
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.Group Permission') }}</h4>
                        </div>
                        {!! Form::open(['route' => 'role.setPermission', 'method' => 'post']) !!}
                        <div class="card-body">
                            <input type="hidden" name="role_id" value="{{ $lims_role_data->id }}" />
                            <div class="table-responsive">
                                <table class="table table-bordered permission-table">
                                    <style>
                                        /* Bold module names (2nd column) */
                                        .permission-table tbody tr td:nth-child(2) {
                                            font-weight: 700;
                                        }

                                        /* Gray out entire row when blocked */
                                        .permission-table tbody tr.blocked-row td {
                                            color: #6c757d !important;
                                        }

                                        .permission-table tbody tr.blocked-row label {
                                            color: #6c757d !important;
                                            cursor: not-allowed !important;
                                        }

                                        /* When blocked, module name (2nd col) should NOT be bold */
                                        .permission-table tbody tr.blocked-row td:nth-child(2) {
                                            font-weight: 400 !important;
                                        }

                                        /* Dim and disable interaction visually on blocked rows */
                                        .permission-table tbody tr.blocked-row td:not(:first-child) {
                                            opacity: 0.55;
                                        }

                                        .permission-table tbody tr.blocked-row input[type="checkbox"] {
                                            pointer-events: none;
                                        }
                                    </style>
                                    <thead>
                                        <tr>
                                            <th colspan="6" class="text-center">{{ $lims_role_data->name }}
                                                {{ trans('file.Group Permission') }}
                                            </th>
                                        </tr>

                                        {{-- Reservations row will be placed at the end of the permissions table --}}
                                        <tr>
                                            <th rowspan="2" class="text-center">Bloquear</th>
                                            <th rowspan="2" class="text-center">Module Name</th>
                                            <th colspan="4" class="text-center">
                                                <div class="checkbox">
                                                    <input type="checkbox" id="select_all">
                                                    <label for="select_all">{{ trans('file.Permissions') }}</label>
                                                </div>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">{{ trans('file.View') }}</th>
                                            <th class="text-center">{{ trans('file.add') }}</th>
                                            <th class="text-center">{{ trans('file.edit') }}</th>
                                            <th class="text-center">{{ trans('file.delete') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('products', $blocked_modules))
                                                            <input type="checkbox" id="products-blocked"
                                                                name="blocked_modules[]" value="products" checked />
                                                        @else
                                                            <input type="checkbox" id="products-blocked"
                                                                name="blocked_modules[]" value="products" />
                                                        @endif
                                                        <label for="products-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.product') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('products-index', $all_permission))
                                                            <input type="checkbox" value="1" id="products-index"
                                                                name="products-index" checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="products-index"
                                                                name="products-index" />
                                                        @endif
                                                        <label for="products-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('products-add', $all_permission))
                                                            <input type="checkbox" value="1" id="products-add"
                                                                name="products-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="products-add"
                                                                name="products-add">
                                                        @endif
                                                        <label for="products-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('products-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="products-edit"
                                                                name="products-edit" checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="products-edit"
                                                                name="products-edit" />
                                                        @endif
                                                        <label for="products-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('products-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="products-delete"
                                                                name="products-delete" checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="products-delete"
                                                                name="products-delete" />
                                                        @endif
                                                        <label for="products-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchases', $blocked_modules))
                                                            <input type="checkbox" id="purchases-blocked"
                                                                name="blocked_modules[]" value="purchases" checked />
                                                        @else
                                                            <input type="checkbox" id="purchases-blocked"
                                                                name="blocked_modules[]" value="purchases" />
                                                        @endif
                                                        <label for="purchases-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Purchase') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchases-index', $all_permission))
                                                            <input type="checkbox" value="1" id="purchases-index"
                                                                name="purchases-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="purchases-index"
                                                                name="purchases-index">
                                                        @endif
                                                        <label for="purchases-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchases-add', $all_permission))
                                                            <input type="checkbox" value="1" id="purchases-add"
                                                                name="purchases-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="purchases-add"
                                                                name="purchases-add">
                                                        @endif
                                                        <label for="purchases-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchases-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="purchases-edit"
                                                                name="purchases-edit" checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="purchases-edit"
                                                                name="purchases-edit">
                                                        @endif
                                                        <label for="purchases-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchases-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="purchases-delete"
                                                                name="purchases-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="purchases-delete"
                                                                name="purchases-delete">
                                                        @endif
                                                        <label for="purchases-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('presale', $blocked_modules))
                                                            <input type="checkbox" id="presale-blocked" name="blocked_modules[]"
                                                                value="presale" checked />
                                                        @else
                                                            <input type="checkbox" id="presale-blocked" name="blocked_modules[]"
                                                                value="presale" />
                                                        @endif
                                                        <label for="presale-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Pre Sale') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('presale-index', $all_permission))
                                                            <input type="checkbox" value="1" id="presale-index"
                                                                name="presale-index" checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="presale-index"
                                                                name="presale-index">
                                                        @endif
                                                        <label for="presale-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('presale-create', $all_permission))
                                                            <input type="checkbox" value="1" id="presale-create"
                                                                name="presale-create" checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="presale-create"
                                                                name="presale-create">
                                                        @endif
                                                        <label for="presale-create"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('presale-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="presale-edit"
                                                                name="presale-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="presale-edit"
                                                                name="presale-edit">
                                                        @endif
                                                        <label for="presale-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('presale-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="presale-delete"
                                                                name="presale-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="presale-delete"
                                                                name="presale-delete">
                                                        @endif
                                                        <label for="presale-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('sales', $blocked_modules))
                                                            <input type="checkbox" id="sales-blocked" name="blocked_modules[]"
                                                                value="sales" checked />
                                                        @else
                                                            <input type="checkbox" id="sales-blocked" name="blocked_modules[]"
                                                                value="sales" />
                                                        @endif
                                                        <label for="sales-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Sale') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('sales-index', $all_permission))
                                                            <input type="checkbox" value="1" id="sales-index" name="sales-index"
                                                                checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="sales-index"
                                                                name="sales-index">
                                                        @endif
                                                        <label for="sales-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('sales-add', $all_permission))
                                                            <input type="checkbox" value="1" id="sales-add" name="sales-add"
                                                                checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="sales-add" name="sales-add">
                                                        @endif
                                                        <label for="sales-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('sales-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="sales-edit" name="sales-edit"
                                                                checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="sales-edit" name="sales-edit">
                                                        @endif
                                                        <label for="sales-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('sales-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="sales-delete"
                                                                name="sales-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="sales-delete"
                                                                name="sales-delete">
                                                        @endif
                                                        <label for="sales-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('expenses', $blocked_modules))
                                                            <input type="checkbox" id="expenses-blocked"
                                                                name="blocked_modules[]" value="expenses" checked />
                                                        @else
                                                            <input type="checkbox" id="expenses-blocked"
                                                                name="blocked_modules[]" value="expenses" />
                                                        @endif
                                                        <label for="expenses-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Expense') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('expenses-index', $all_permission))
                                                            <input type="checkbox" value="1" id="expenses-index"
                                                                name="expenses-index" checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="expenses-index"
                                                                name="expenses-index">
                                                        @endif
                                                        <label for="expenses-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('expenses-add', $all_permission))
                                                            <input type="checkbox" value="1" id="expenses-add"
                                                                name="expenses-add" checked />
                                                        @else
                                                            <input type="checkbox" value="1" id="expenses-add"
                                                                name="expenses-add">
                                                        @endif
                                                        <label for="expenses-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('expenses-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="expenses-edit"
                                                                name="expenses-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="expenses-edit"
                                                                name="expenses-edit">
                                                        @endif
                                                        <label for="expenses-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('expenses-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="expenses-delete"
                                                                name="expenses-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="expenses-delete"
                                                                name="expenses-delete">
                                                        @endif
                                                        <label for="expenses-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('quotations', $blocked_modules))
                                                            <input type="checkbox" id="quotations-blocked"
                                                                name="blocked_modules[]" value="quotations" checked />
                                                        @else
                                                            <input type="checkbox" id="quotations-blocked"
                                                                name="blocked_modules[]" value="quotations" />
                                                        @endif
                                                        <label for="quotations-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Quotation') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('quotes-index', $all_permission))
                                                            <input type="checkbox" value="1" id="quotes-index"
                                                                name="quotes-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="quotes-index"
                                                                name="quotes-index">
                                                        @endif
                                                        <label for="quotes-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('quotes-add', $all_permission))
                                                            <input type="checkbox" value="1" id="quotes-add" name="quotes-add"
                                                                checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="quotes-add" name="quotes-add">
                                                        @endif
                                                        <label for="quotes-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('quotes-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="quotes-edit" name="quotes-edit"
                                                                checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="quotes-edit"
                                                                name="quotes-edit">
                                                        @endif
                                                        <label for="quotes-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('quotes-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="quotes-delete"
                                                                name="quotes-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="quotes-delete"
                                                                name="quotes-delete">
                                                        @endif
                                                        <label for="quotes-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue">
                                                    <div class="checkbox">
                                                        @if (in_array('transfers', $blocked_modules))
                                                            <input type="checkbox" id="transfers-blocked"
                                                                name="blocked_modules[]" value="transfers" checked />
                                                        @else
                                                            <input type="checkbox" id="transfers-blocked"
                                                                name="blocked_modules[]" value="transfers" />
                                                        @endif
                                                        <label for="transfers-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Transfer') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('transfers-index', $all_permission))
                                                            <input type="checkbox" value="1" id="transfers-index"
                                                                name="transfers-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="transfers-index"
                                                                name="transfers-index">
                                                        @endif
                                                        <label for="transfers-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('transfers-add', $all_permission))
                                                            <input type="checkbox" value="1" id="transfers-add"
                                                                name="transfers-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="transfers-add"
                                                                name="transfers-add">
                                                        @endif
                                                        <label for="transfers-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('transfers-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="transfers-edit"
                                                                name="transfers-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="transfers-edit"
                                                                name="transfers-edit">
                                                        @endif
                                                        <label for="transfers-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('transfers-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="transfers-delete"
                                                                name="transfers-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="transfers-delete"
                                                                name="transfers-delete">
                                                        @endif
                                                        <label for="transfers-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('returns', $blocked_modules))
                                                            <input type="checkbox" id="returns-blocked" name="blocked_modules[]"
                                                                value="returns" checked />
                                                        @else
                                                            <input type="checkbox" id="returns-blocked" name="blocked_modules[]"
                                                                value="returns" />
                                                        @endif
                                                        <label for="returns-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Sale Return') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('returns-index', $all_permission))
                                                            <input type="checkbox" value="1" id="returns-index"
                                                                name="returns-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="returns-index"
                                                                name="returns-index">
                                                        @endif
                                                        <label for="returns-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('returns-add', $all_permission))
                                                            <input type="checkbox" value="1" id="returns-add" name="returns-add"
                                                                checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="returns-add"
                                                                name="returns-add">
                                                        @endif
                                                        <label for="returns-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('returns-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="returns-edit"
                                                                name="returns-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="returns-edit"
                                                                name="returns-edit">
                                                        @endif
                                                        <label for="returns-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('returns-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="returns-delete"
                                                                name="returns-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="returns-delete"
                                                                name="returns-delete">
                                                        @endif
                                                        <label for="returns-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchase-returns', $blocked_modules))
                                                            <input type="checkbox" id="purchase-returns-blocked"
                                                                name="blocked_modules[]" value="purchase-returns" checked />
                                                        @else
                                                            <input type="checkbox" id="purchase-returns-blocked"
                                                                name="blocked_modules[]" value="purchase-returns" />
                                                        @endif
                                                        <label for="purchase-returns-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Purchase Return') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchase-return-index', $all_permission))
                                                            <input type="checkbox" value="1" id="purchase-return-index"
                                                                name="purchase-return-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="purchase-return-index"
                                                                name="purchase-return-index">
                                                        @endif
                                                        <label for="purchase-return-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchase-return-add', $all_permission))
                                                            <input type="checkbox" value="1" id="purchase-return-add"
                                                                name="purchase-return-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="purchase-return-add"
                                                                name="purchase-return-add">
                                                        @endif
                                                        <label for="purchase-return-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchase-return-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="purchase-return-edit"
                                                                name="purchase-return-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="purchase-return-edit"
                                                                name="purchase-return-edit">
                                                        @endif
                                                        <label for="purchase-return-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('purchase-return-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="purchase-return-delete"
                                                                name="purchase-return-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="purchase-return-delete"
                                                                name="purchase-return-delete">
                                                        @endif
                                                        <label for="purchase-return-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('qty_adjustment', $blocked_modules))
                                                            <input type="checkbox" id="qty_adjustment-blocked"
                                                                name="blocked_modules[]" value="qty_adjustment" checked />
                                                        @else
                                                            <input type="checkbox" id="qty_adjustment-blocked"
                                                                name="blocked_modules[]" value="qty_adjustment" />
                                                        @endif
                                                        <label for="qty_adjustment-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Adjustment') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('qty_adjustment-index', $all_permission))
                                                            <input type="checkbox" value="1" id="qty_adjustment-index"
                                                                name="qty_adjustment-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="qty_adjustment-index"
                                                                name="qty_adjustment-index">
                                                        @endif
                                                        <label for="qty_adjustment-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('qty_adjustment-add', $all_permission))
                                                            <input type="checkbox" value="1" id="qty_adjustment-add"
                                                                name="qty_adjustment-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="qty_adjustment-add"
                                                                name="qty_adjustment-add">
                                                        @endif
                                                        <label for="qty_adjustment-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('qty_adjustment-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="qty_adjustment-edit"
                                                                name="qty_adjustment-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="qty_adjustment-edit"
                                                                name="qty_adjustment-edit">
                                                        @endif
                                                        <label for="qty_adjustment-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('qty_adjustment-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="qty_adjustment-delete"
                                                                name="qty_adjustment-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="qty_adjustment-delete"
                                                                name="qty_adjustment-delete">
                                                        @endif
                                                        <label for="qty_adjustment-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('employee', $blocked_modules))
                                                            <input type="checkbox" id="employee-blocked"
                                                                name="blocked_modules[]" value="employee" checked />
                                                        @else
                                                            <input type="checkbox" id="employee-blocked"
                                                                name="blocked_modules[]" value="employee" />
                                                        @endif
                                                        <label for="employee-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Employee') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('employees-index', $all_permission))
                                                            <input type="checkbox" value="1" id="employees-index"
                                                                name="employees-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="employees-index"
                                                                name="employees-index">
                                                        @endif
                                                        <label for="employees-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('employees-add', $all_permission))
                                                            <input type="checkbox" value="1" id="employees-add"
                                                                name="employees-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="employees-add"
                                                                name="employees-add">
                                                        @endif
                                                        <label for="employees-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('employees-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="employees-edit"
                                                                name="employees-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="employees-edit"
                                                                name="employees-edit">
                                                        @endif
                                                        <label for="employees-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('employees-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="employees-delete"
                                                                name="employees-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="employees-delete"
                                                                name="employees-delete">
                                                        @endif
                                                        <label for="employees-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('adjustment-account', $blocked_modules))
                                                            <input type="checkbox" id="adjustment-account-blocked"
                                                                name="blocked_modules[]" value="adjustment-account" checked />
                                                        @else
                                                            <input type="checkbox" id="adjustment-account-blocked"
                                                                name="blocked_modules[]" value="adjustment-account" />
                                                        @endif
                                                        <label for="adjustment-account-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Adjustment List') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('adjustment-account-index', $all_permission))
                                                            <input type="checkbox" value="1" id="adjustment-account-index"
                                                                name="adjustment-account-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="adjustment-account-index"
                                                                name="adjustment-account-index">
                                                        @endif
                                                        <label for="adjustment-account-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('adjustment-account-add', $all_permission))
                                                            <input type="checkbox" value="1" id="adjustment-account-add"
                                                                name="adjustment-account-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="adjustment-account-add"
                                                                name="adjustment-account-add">
                                                        @endif
                                                        <label for="adjustment-account-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('adjustment-account-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="adjustment-account-edit"
                                                                name="adjustment-account-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="adjustment-account-edit"
                                                                name="adjustment-account-edit">
                                                        @endif
                                                        <label for="adjustment-account-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('adjustment-account-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="adjustment-account-delete"
                                                                name="adjustment-account-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="adjustment-account-delete"
                                                                name="adjustment-account-delete">
                                                        @endif
                                                        <label for="adjustment-account-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('user', $blocked_modules))
                                                            <input type="checkbox" id="user-blocked" name="blocked_modules[]"
                                                                value="user" checked />
                                                        @else
                                                            <input type="checkbox" id="user-blocked" name="blocked_modules[]"
                                                                value="user" />
                                                        @endif
                                                        <label for="user-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.User') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('users-index', $all_permission))
                                                            <input type="checkbox" value="1" id="users-index" name="users-index"
                                                                checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="users-index"
                                                                name="users-index">
                                                        @endif
                                                        <label for="users-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('users-add', $all_permission))
                                                            <input type="checkbox" value="1" id="users-add" name="users-add"
                                                                checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="users-add" name="users-add">
                                                        @endif
                                                        <label for="users-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('users-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="users-edit" name="users-edit"
                                                                checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="users-edit" name="users-edit">
                                                        @endif
                                                        <label for="users-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('users-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="users-delete"
                                                                name="users-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="users-delete"
                                                                name="users-delete">
                                                        @endif
                                                        <label for="users-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('customer', $blocked_modules))
                                                            <input type="checkbox" id="customer-blocked"
                                                                name="blocked_modules[]" value="customer" checked />
                                                        @else
                                                            <input type="checkbox" id="customer-blocked"
                                                                name="blocked_modules[]" value="customer" />
                                                        @endif
                                                        <label for="customer-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.customer') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('customers-index', $all_permission))
                                                            <input type="checkbox" value="1" id="customers-index"
                                                                name="customers-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="customers-index"
                                                                name="customers-index">
                                                        @endif
                                                        <label for="customers-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('customers-add', $all_permission))
                                                            <input type="checkbox" value="1" id="customers-add"
                                                                name="customers-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="customers-add"
                                                                name="customers-add">
                                                        @endif
                                                        <label for="customers-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('customers-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="customers-edit"
                                                                name="customers-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="customers-edit"
                                                                name="customers-edit">
                                                        @endif
                                                        <label for="customers-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('customers-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="customers-delete"
                                                                name="customers-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="customers-delete"
                                                                name="customers-delete">
                                                        @endif
                                                        <label for="customers-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('biller', $blocked_modules))
                                                            <input type="checkbox" id="biller-blocked" name="blocked_modules[]"
                                                                value="biller" checked />
                                                        @else
                                                            <input type="checkbox" id="biller-blocked" name="blocked_modules[]"
                                                                value="biller" />
                                                        @endif
                                                        <label for="biller-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Biller') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('billers-index', $all_permission))
                                                            <input type="checkbox" value="1" id="billers-index"
                                                                name="billers-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="billers-index"
                                                                name="billers-index">
                                                        @endif
                                                        <label for="billers-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('billers-add', $all_permission))
                                                            <input type="checkbox" value="1" id="billers-add" name="billers-add"
                                                                checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="billers-add"
                                                                name="billers-add">
                                                        @endif
                                                        <label for="billers-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue checked" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('billers-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="billers-edit"
                                                                name="billers-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="billers-edit"
                                                                name="billers-edit">
                                                        @endif
                                                        <label for="billers-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('billers-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="billers-delete"
                                                                name="billers-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="billers-delete"
                                                                name="billers-delete">
                                                        @endif
                                                        <label for="billers-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('supplier', $blocked_modules))
                                                            <input type="checkbox" id="supplier-blocked"
                                                                name="blocked_modules[]" value="supplier" checked />
                                                        @else
                                                            <input type="checkbox" id="supplier-blocked"
                                                                name="blocked_modules[]" value="supplier" />
                                                        @endif
                                                        <label for="supplier-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Supplier') }}</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('suppliers-index', $all_permission))
                                                            <input type="checkbox" value="1" id="suppliers-index"
                                                                name="suppliers-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="suppliers-index"
                                                                name="suppliers-index">
                                                        @endif
                                                        <label for="suppliers-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('suppliers-add', $all_permission))
                                                            <input type="checkbox" value="1" id="suppliers-add"
                                                                name="suppliers-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="suppliers-add"
                                                                name="suppliers-add">
                                                        @endif
                                                        <label for="suppliers-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('suppliers-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="suppliers-edit"
                                                                name="suppliers-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="suppliers-edit"
                                                                name="suppliers-edit">
                                                        @endif
                                                        <label for="suppliers-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('suppliers-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="suppliers-delete"
                                                                name="suppliers-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="suppliers-delete"
                                                                name="suppliers-delete">
                                                        @endif
                                                        <label for="suppliers-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('accounting', $blocked_modules))
                                                            <input type="checkbox" id="accounting-blocked"
                                                                name="blocked_modules[]" value="accounting" checked />
                                                        @else
                                                            <input type="checkbox" id="accounting-blocked"
                                                                name="blocked_modules[]" value="accounting" />
                                                        @endif
                                                        <label for="accounting-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Accounting') }}</td>
                                            <td class="report-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('account-index', $all_permission))
                                                                <input type="checkbox" value="1" id="account-index"
                                                                    name="account-index" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="account-index"
                                                                    name="account-index">
                                                            @endif
                                                            <label for="account-index"
                                                                class="padding05">{{ trans('file.Account') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('money-transfer', $all_permission))
                                                                <input type="checkbox" value="1" id="money-transfer"
                                                                    name="money-transfer" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="money-transfer"
                                                                    name="money-transfer">
                                                            @endif
                                                            <label for="money-transfer"
                                                                class="padding05">{{ trans('file.Money Transfer') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('balance-sheet', $all_permission))
                                                                <input type="checkbox" value="1" id="balance-sheet"
                                                                    name="balance-sheet" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="balance-sheet"
                                                                    name="balance-sheet">
                                                            @endif
                                                            <label for="balance-sheet"
                                                                class="padding05">{{ trans('file.Balance Sheet') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('balance-sheet-account', $all_permission))
                                                                <input type="checkbox" value="1" id="balance-sheet-account"
                                                                    name="balance-sheet-account" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="balance-sheet-account"
                                                                    name="balance-sheet-account">
                                                            @endif
                                                            <label for="balance-sheet-account" class="padding05">Arqueo Caja
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('close-balance-account', $all_permission))
                                                                <input type="checkbox" value="1" id="close-balance-account"
                                                                    name="close-balance-account" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="close-balance-account"
                                                                    name="close-balance-account">
                                                            @endif
                                                            <label for="close-balance-account" class="padding05">Cierre de
                                                                Cajas
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('account-statement', $all_permission))
                                                                <input type="checkbox" value="1" id="account-statement"
                                                                    name="account-statement" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="account-statement"
                                                                    name="account-statement">
                                                            @endif
                                                            <label for="account-statement"
                                                                class="padding05">{{ trans('file.Account Statement') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('hrm', $blocked_modules))
                                                            <input type="checkbox" id="hrm-blocked" name="blocked_modules[]"
                                                                value="hrm" checked />
                                                        @else
                                                            <input type="checkbox" id="hrm-blocked" name="blocked_modules[]"
                                                                value="hrm" />
                                                        @endif
                                                        <label for="hrm-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>HRM</td>
                                            <td class="report-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('hrm-menu', $all_permission))
                                                                <input type="checkbox" value="1" id="hrm-menu" name="hrm-menu"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="hrm-menu" name="hrm-menu">
                                                            @endif
                                                            <label for="hrm-menu" class="padding05">{{ trans('file.HRM') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('department', $all_permission))
                                                                <input type="checkbox" value="1" id="department"
                                                                    name="department" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="department"
                                                                    name="department">
                                                            @endif
                                                            <label for="department"
                                                                class="padding05">{{ trans('file.Department') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('attendance', $all_permission))
                                                                <input type="checkbox" value="1" id="attendance"
                                                                    name="attendance" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="attendance"
                                                                    name="attendance">
                                                            @endif
                                                            <label for="attendance"
                                                                class="padding05">{{ trans('file.Attendance') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('payroll', $all_permission))
                                                                <input type="checkbox" value="1" id="payroll" name="payroll"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="payroll" name="payroll">
                                                            @endif
                                                            <label for="payroll"
                                                                class="padding05">{{ trans('file.Payroll') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('holiday', $all_permission))
                                                                <input type="checkbox" value="1" id="holiday" name="holiday"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="holiday" name="holiday">
                                                            @endif
                                                            <label for="holiday"
                                                                class="padding05">{{ trans('file.Holiday Approve') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('attentionshift', $all_permission))
                                                                <input type="checkbox" value="1" id="attentionshift"
                                                                    name="attentionshift" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="attentionshift"
                                                                    name="attentionshift">
                                                            @endif
                                                            <label for="attentionshift"
                                                                class="padding05">{{ trans('file.Attention Shift') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                {{-- Reservas moved to its own row below --}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('reports', $blocked_modules))
                                                            <input type="checkbox" id="reports-blocked" name="blocked_modules[]"
                                                                value="reports" checked />
                                                        @else
                                                            <input type="checkbox" id="reports-blocked" name="blocked_modules[]"
                                                                value="reports" />
                                                        @endif
                                                        <label for="reports-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.Reports') }}</td>
                                            <td class="report-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('profit-loss', $all_permission))
                                                                <input type="checkbox" value="1" id="profit-loss"
                                                                    name="profit-loss" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="profit-loss"
                                                                    name="profit-loss">
                                                            @endif
                                                            <label for="profit-loss"
                                                                class="padding05">{{ trans('file.Summary Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('best-seller', $all_permission))
                                                                <input type="checkbox" value="1" id="best-seller"
                                                                    name="best-seller" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="best-seller"
                                                                    name="best-seller">
                                                            @endif
                                                            <label for="best-seller"
                                                                class="padding05">{{ trans('file.Best Seller') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('daily-sale', $all_permission))
                                                                <input type="checkbox" value="1" id="daily-sale"
                                                                    name="daily-sale" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="daily-sale"
                                                                    name="daily-sale">
                                                            @endif
                                                            <label for="daily-sale"
                                                                class="padding05">{{ trans('file.Daily Sale') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('monthly-sale', $all_permission))
                                                                <input type="checkbox" value="1" id="monthly-sale"
                                                                    name="monthly-sale" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="monthly-sale"
                                                                    name="monthly-sale">
                                                            @endif
                                                            <label for="monthly-sale"
                                                                class="padding05">{{ trans('file.Monthly Sale') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('daily-purchase', $all_permission))
                                                                <input type="checkbox" value="1" id="daily-purchase"
                                                                    name="daily-purchase" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="daily-purchase"
                                                                    name="daily-purchase">
                                                            @endif
                                                            <label for="daily-purchase"
                                                                class="padding05">{{ trans('file.Daily Purchase') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('monthly-purchase', $all_permission))
                                                                <input type="checkbox" value="1" id="monthly-purchase"
                                                                    name="monthly-purchase" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="monthly-purchase"
                                                                    name="monthly-purchase">
                                                            @endif
                                                            <label for="monthly-purchase"
                                                                class="padding05">{{ trans('file.Monthly Purchase') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('product-report', $all_permission))
                                                                <input type="checkbox" value="1" id="product-report"
                                                                    name="product-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="product-report"
                                                                    name="product-report">
                                                            @endif
                                                            <label for="product-report"
                                                                class="padding05">{{ trans('file.Product Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('product-detail-report', $all_permission))
                                                                <input type="checkbox" value="1" id="product-detail-report"
                                                                    name="product-detail-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="product-detail-report"
                                                                    name="product-detail-report">
                                                            @endif
                                                            <label for="product-detail-report" class="padding05">Informe
                                                                Producto Por Precio
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('payment-report', $all_permission))
                                                                <input type="checkbox" value="1" id="payment-report"
                                                                    name="payment-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="payment-report"
                                                                    name="payment-report">
                                                            @endif
                                                            <label for="payment-report"
                                                                class="padding05">{{ trans('file.Payment Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('purchase-report', $all_permission))
                                                                <input type="checkbox" value="1" id="purchase-report"
                                                                    name="purchase-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="purchase-report"
                                                                    name="purchase-report">
                                                            @endif
                                                            <label for="purchase-report" class="padding05">
                                                                {{ trans('file.Purchase Report') }} &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('sale-report', $all_permission))
                                                                <input type="checkbox" value="1" id="sale-report"
                                                                    name="sale-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="sale-report"
                                                                    name="sale-report">
                                                            @endif
                                                            <label for="sale-report"
                                                                class="padding05">{{ trans('file.Sale Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('saledetail-report', $all_permission))
                                                                <input type="checkbox" value="1" id="saledetail-report"
                                                                    name="saledetail-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="saledetail-report"
                                                                    name="saledetail-report">
                                                            @endif
                                                            <label for="saledetail-report"
                                                                class="padding05">{{ trans('file.Sale Items Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('warehouse-report', $all_permission))
                                                                <input type="checkbox" value="1" id="warehouse-report"
                                                                    name="warehouse-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="warehouse-report"
                                                                    name="warehouse-report">
                                                            @endif
                                                            <label for="warehouse-report"
                                                                class="padding05">{{ trans('file.Warehouse Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('warehouse-stock-report', $all_permission))
                                                                <input type="checkbox" value="1" id="warehouse-stock-report"
                                                                    name="warehouse-stock-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="warehouse-stock-report"
                                                                    name="warehouse-stock-report">
                                                            @endif
                                                            <label for="warehouse-stock-report"
                                                                class="padding05">{{ trans('file.Warehouse Stock Chart') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('product-qty-alert', $all_permission))
                                                                <input type="checkbox" value="1" id="product-qty-alert"
                                                                    name="product-qty-alert" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="product-qty-alert"
                                                                    name="product-qty-alert">
                                                            @endif
                                                            <label for="product-qty-alert"
                                                                class="padding05">{{ trans('file.Product Quantity Alert') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('user-report', $all_permission))
                                                                <input type="checkbox" value="1" id="user-report"
                                                                    name="user-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="user-report"
                                                                    name="user-report">
                                                            @endif
                                                            <label for="user-report"
                                                                class="padding05">{{ trans('file.User Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('customer-report', $all_permission))
                                                                <input type="checkbox" value="1" id="customer-report"
                                                                    name="customer-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="customer-report"
                                                                    name="customer-report">
                                                            @endif
                                                            <label for="customer-report"
                                                                class="padding05">{{ trans('file.Customer Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('supplier-report', $all_permission))
                                                                <input type="checkbox" value="1" id="supplier-report"
                                                                    name="supplier-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="supplier-report"
                                                                    name="supplier-report">
                                                            @endif
                                                            <label for="Supplier-report"
                                                                class="padding05">{{ trans('file.Supplier Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('due-report', $all_permission))
                                                                <input type="checkbox" value="1" id="due-report"
                                                                    name="due-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="due-report"
                                                                    name="due-report">
                                                            @endif
                                                            <label for="due-report"
                                                                class="padding05">{{ trans('file.Due Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('salecustomer-report', $all_permission))
                                                                <input type="checkbox" value="1" id="salecustomer-report"
                                                                    name="salecustomer-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="salecustomer-report"
                                                                    name="salecustomer-report">
                                                            @endif
                                                            <label for="salecustomer-report"
                                                                class="padding05">{{ trans('file.Sale Customer Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('salebiller-report', $all_permission))
                                                                <input type="checkbox" value="1" id="salebiller-report"
                                                                    name="salebiller-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="salebiller-report"
                                                                    name="salebiller-report">
                                                            @endif
                                                            <label for="salebiller-report"
                                                                class="padding05">{{ trans('file.Sale Biller Report') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('only-commision-report', $all_permission))
                                                                <input type="checkbox" value="1" id="only-commision-report"
                                                                    name="only-commision-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="only-commision-report"
                                                                    name="only-commision-report">
                                                            @endif
                                                            <label for="only-commision-report"
                                                                class="padding05">{{ trans('file.Employee Only Comission') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('service-commission-report', $all_permission))
                                                                <input type="checkbox" value="1" id="service-commission-report"
                                                                    name="service-commission-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="service-commission-report"
                                                                    name="service-commission-report">
                                                            @endif
                                                            <label for="service-commission-report"
                                                                class="padding05">{{ trans('file.Employee Service Comission') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('sale-renueve-report', $all_permission))
                                                                <input type="checkbox" value="1" id="sale-renueve-report"
                                                                    name="sale-renueve-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="sale-renueve-report"
                                                                    name="sale-renueve-report">
                                                            @endif
                                                            <label for="sale-renueve-report"
                                                                class="padding05">{{ trans('file.Report Product Renueve') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('attendance-employee-report', $all_permission))
                                                                <input type="checkbox" value="1" id="attendance-employee-report"
                                                                    name="attendance-employee-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="attendance-employee-report"
                                                                    name="attendance-employee-report">
                                                            @endif
                                                            <label for="attendance-employee-report"
                                                                class="padding05">{{ trans('file.Report Attendance By Employee') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('holiday-employee-report', $all_permission))
                                                                <input type="checkbox" value="1" id="holiday-employee-report"
                                                                    name="holiday-employee-report" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="holiday-employee-report"
                                                                    name="holiday-employee-report">
                                                            @endif
                                                            <label for="holiday-employee-report"
                                                                class="padding05">{{ trans('file.Report Holiday By Employee') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue">
                                                    <div class="checkbox">
                                                        @if (in_array('settings', $blocked_modules))
                                                            <input type="checkbox" id="settings-blocked"
                                                                name="blocked_modules[]" value="settings" checked />
                                                        @else
                                                            <input type="checkbox" id="settings-blocked"
                                                                name="blocked_modules[]" value="settings" />
                                                        @endif
                                                        <label for="settings-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ trans('file.settings') }}</td>
                                            <td class="report-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('warehouse', $all_permission))
                                                                <input type="checkbox" value="1" id="warehouse" name="warehouse"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="warehouse"
                                                                    name="warehouse">
                                                            @endif
                                                            <label for="warehouse"
                                                                class="padding05">{{ trans('file.Warehouse') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('customer_group', $all_permission))
                                                                <input type="checkbox" value="1" id="customer_group"
                                                                    name="customer_group" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="customer_group"
                                                                    name="customer_group">
                                                            @endif
                                                            <label for="customer_group"
                                                                class="padding05">{{ trans('file.Customer Group') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('brand', $all_permission))
                                                                <input type="checkbox" value="1" id="brand" name="brand"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="brand" name="brand">
                                                            @endif
                                                            <label for="brand" class="padding05">{{ trans('file.Brand') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('unit', $all_permission))
                                                                <input type="checkbox" value="1" id="unit" name="unit" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="unit" name="unit">
                                                            @endif
                                                            <label for="unit" class="padding05">{{ trans('file.Unit') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('tax', $all_permission))
                                                                <input type="checkbox" value="1" id="tax" name="tax" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="tax" name="tax">
                                                            @endif
                                                            <label for="tax" class="padding05">{{ trans('file.Tax') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('general_setting', $all_permission))
                                                                <input type="checkbox" value="1" id="general_setting"
                                                                    name="general_setting" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="general_setting"
                                                                    name="general_setting">
                                                            @endif
                                                            <label for="general_setting"
                                                                class="padding05">{{ trans('file.General Setting') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('mail_setting', $all_permission))
                                                                <input type="checkbox" value="1" id="mail_setting"
                                                                    name="mail_setting" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="mail_setting"
                                                                    name="mail_setting">
                                                            @endif
                                                            <label for="mail_setting"
                                                                class="padding05">{{ trans('file.Mail Setting') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('sms_setting', $all_permission))
                                                                <input type="checkbox" value="1" id="sms_setting"
                                                                    name="sms_setting" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="sms_setting"
                                                                    name="sms_setting">
                                                            @endif
                                                            <label for="sms_setting"
                                                                class="padding05">{{ trans('file.SMS Setting') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('create_sms', $all_permission))
                                                                <input type="checkbox" value="1" id="create_sms"
                                                                    name="create_sms" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="create_sms"
                                                                    name="create_sms">
                                                            @endif
                                                            <label for="create_sms"
                                                                class="padding05">{{ trans('file.Create SMS') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_setting', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_setting"
                                                                    name="pos_setting" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_setting"
                                                                    name="pos_setting">
                                                            @endif
                                                            <label for="pos_setting"
                                                                class="padding05">{{ trans('file.POS Setting') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('hrm_setting', $all_permission))
                                                                <input type="checkbox" value="1" id="hrm_setting"
                                                                    name="hrm_setting" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="hrm_setting"
                                                                    name="hrm_setting">
                                                            @endif
                                                            <label for="hrm_setting"
                                                                class="padding05">{{ trans('file.HRM Setting') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('module_qr', $all_permission))
                                                                <input type="checkbox" value="1" id="module_qr" name="module_qr"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="module_qr"
                                                                    name="module_qr">
                                                            @endif
                                                            <label for="module_qr"
                                                                class="padding05">{{ trans('file.QR Setting') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('module_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="module_siat"
                                                                    name="module_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="module_siat"
                                                                    name="module_siat">
                                                            @endif
                                                            <label for="module_siat"
                                                                class="padding05">{{ trans('file.Settings SIAT') }}
                                                                <span>(Panel SIAT)</span>
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr id="panel-siat">
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('panel_siat', $blocked_modules))
                                                            <input type="checkbox" id="panel_siat-blocked"
                                                                name="blocked_modules[]" value="panel_siat" checked />
                                                        @else
                                                            <input type="checkbox" id="panel_siat-blocked"
                                                                name="blocked_modules[]" value="panel_siat" />
                                                        @endif
                                                        <label for="panel_siat-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Panel SIAT</td>
                                            <td class="siat-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('panel_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="panel_siat"
                                                                    name="panel_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="panel_siat"
                                                                    name="panel_siat">
                                                            @endif
                                                            <label for="panel_siat" class="padding05">Panel Siat
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('sucursal_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="sucursal_siat"
                                                                    name="sucursal_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="sucursal_siat"
                                                                    name="sucursal_siat">
                                                            @endif
                                                            <label for="sucursal_siat" class="padding05">Sucursal Siat
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('puntoventa_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="puntoventa_siat"
                                                                    name="puntoventa_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="puntoventa_siat"
                                                                    name="puntoventa_siat">
                                                            @endif
                                                            <label for="puntoventa_siat" class="padding05">Punto Venta
                                                                Siat &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('urlws_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="urlws_siat"
                                                                    name="urlws_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="urlws_siat"
                                                                    name="urlws_siat">
                                                            @endif
                                                            <label for="urlws_siat" class="padding05">URL WS
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('authfact_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="authfact_siat"
                                                                    name="authfact_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="authfact_siat"
                                                                    name="authfact_siat">
                                                            @endif
                                                            <label for="authfact_siat"
                                                                class="padding05">Autorizacion/Facturación
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('cafc_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="cafc_siat" name="cafc_siat"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="cafc_siat"
                                                                    name="cafc_siat">
                                                            @endif
                                                            <label for="cafc_siat" class="padding05">Credenciales CAFC
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('contingencia_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="contingencia_siat"
                                                                    name="contingencia_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="contingencia_siat"
                                                                    name="contingencia_siat">
                                                            @endif
                                                            <label for="contingencia_siat" class="padding05">Contingencia
                                                                (Menu Ventas)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('facturamasiva_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="facturamasiva_siat"
                                                                    name="facturamasiva_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="facturamasiva_siat"
                                                                    name="facturamasiva_siat">
                                                            @endif
                                                            <label for="facturamasiva_siat" class="padding05">Factura Masiva
                                                                (Menu Ventas)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('notadebcred_siat', $all_permission))
                                                                <input type="checkbox" value="1" id="notadebcred_siat"
                                                                    name="notadebcred_siat" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="notadebcred_siat"
                                                                    name="notadebcred_siat">
                                                            @endif
                                                            <label for="notadebcred_siat" class="padding05">Nota
                                                                Debito/Credito (Regreso - Venta)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('sales-list-booksale', $all_permission))
                                                                <input type="checkbox" value="1" id="sales-list-booksale"
                                                                    name="sales-list-booksale" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="sales-list-booksale"
                                                                    name="sales-list-booksale">
                                                            @endif
                                                            <label for="sales-list-booksale" class="padding05">Libro de
                                                                Ventas (Menu Ventas)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr id="panel-report-siat">
                                            <td></td>
                                            <td>Libro Ventas</td>
                                            <td class="siat-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('lv_arqueogralpdf', $all_permission))
                                                                <input type="checkbox" value="1" id="lv_arqueogralpdf"
                                                                    name="lv_arqueogralpdf" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="lv_arqueogralpdf"
                                                                    name="lv_arqueogralpdf">
                                                            @endif
                                                            <label for="lv_arqueogralpdf" class="padding05">Reporte Arqueo
                                                                General
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('lv_arqueogral_categ', $all_permission))
                                                                <input type="checkbox" value="1" id="lv_arqueogral_categ"
                                                                    name="lv_arqueogral_categ" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="lv_arqueogral_categ"
                                                                    name="lv_arqueogral_categ">
                                                            @endif
                                                            <label for="lv_arqueogral_categ" class="padding05">Reporte
                                                                Arqueo General Categoria
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('lv_reportespdf_excel', $all_permission))
                                                                <input type="checkbox" value="1" id="lv_reportespdf_excel"
                                                                    name="lv_reportespdf_excel" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="lv_reportespdf_excel"
                                                                    name="lv_reportespdf_excel">
                                                            @endif
                                                            <label for="lv_reportespdf_excel" class="padding05">Reportes
                                                                PDF/EXCEL
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('lv_facturas_cobradas', $all_permission))
                                                                <input type="checkbox" value="1" id="lv_facturas_cobradas"
                                                                    name="lv_facturas_cobradas" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="lv_facturas_cobradas"
                                                                    name="lv_facturas_cobradas">
                                                            @endif
                                                            <label for="lv_facturas_cobradas" class="padding05">Reporte
                                                                Facturas Cobradas
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('lv_facturas_revertidas', $all_permission))
                                                                <input type="checkbox" value="1" id="lv_facturas_revertidas"
                                                                    name="lv_facturas_revertidas" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="lv_facturas_revertidas"
                                                                    name="lv_facturas_revertidas">
                                                            @endif
                                                            <label for="lv_facturas_revertidas" class="padding05">Reporte
                                                                Facturas Revertidas
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>{{ trans('file.Miscellaneous') }}</td>
                                            <td class="report-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('category', $all_permission))
                                                                <input type="checkbox" value="1" id="category" name="category"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="category" name="category">
                                                            @endif
                                                            <label for="category"
                                                                class="padding05">{{ trans('file.category') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('delivery', $all_permission))
                                                                <input type="checkbox" value="1" id="delivery" name="delivery"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="delivery" name="delivery">
                                                            @endif
                                                            <label for="delivery"
                                                                class="padding05">{{ trans('file.Delivery') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('stock_count', $all_permission))
                                                                <input type="checkbox" value="1" id="stock_count"
                                                                    name="stock_count" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="stock_count"
                                                                    name="stock_count">
                                                            @endif
                                                            <label for="stock_count"
                                                                class="padding05">{{ trans('file.Stock Count') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('adjustment', $all_permission))
                                                                <input type="checkbox" value="1" id="adjustment"
                                                                    name="adjustment" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="adjustment"
                                                                    name="adjustment">
                                                            @endif
                                                            <label for="adjustment"
                                                                class="padding05">{{ trans('file.Adjustment') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('gift_card', $all_permission))
                                                                <input type="checkbox" value="1" id="gift_card" name="gift_card"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="gift_card"
                                                                    name="gift_card">
                                                            @endif
                                                            <label for="gift_card"
                                                                class="padding05">{{ trans('file.Gift Card') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('coupon', $all_permission))
                                                                <input type="checkbox" value="1" id="coupon" name="coupon"
                                                                    checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="coupon" name="coupon">
                                                            @endif
                                                            <label for="coupon" class="padding05">{{ trans('file.Coupon') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('print_barcode', $all_permission))
                                                                <input type="checkbox" value="1" id="print_barcode"
                                                                    name="print_barcode" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="print_barcode"
                                                                    name="print_barcode">
                                                            @endif
                                                            <label for="print_barcode"
                                                                class="padding05">{{ trans('file.print_barcode') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('sale_pendingdue', $all_permission))
                                                                <input type="checkbox" value="1" id="sale_pendingdue"
                                                                    name="sale_pendingdue" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="sale_pendingdue"
                                                                    name="sale_pendingdue">
                                                            @endif
                                                            <label for="sale_pendingdue"
                                                                class="padding05">{{ trans('file.Sale List') }} Por Pagar
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('empty_database', $all_permission))
                                                                <input type="checkbox" value="1" id="empty_database"
                                                                    name="empty_database" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="empty_database"
                                                                    name="empty_database">
                                                            @endif
                                                            <label for="empty_database"
                                                                class="padding05">{{ trans('file.Empty Database') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('backup_database', $all_permission))
                                                                <input type="checkbox" value="1" id="backup_database"
                                                                    name="backup_database" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="backup_database"
                                                                    name="backup_database">
                                                            @endif
                                                            <label for="backup_database"
                                                                class="padding05">{{ trans('file.Backup Database') }}
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('pos', $blocked_modules))
                                                            <input type="checkbox" id="pos-blocked" name="blocked_modules[]"
                                                                value="pos" checked />
                                                        @else
                                                            <input type="checkbox" id="pos-blocked" name="blocked_modules[]"
                                                                value="pos" />
                                                        @endif
                                                        <label for="pos-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>POS</td>
                                            <td class="pos-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_payment_card', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_payment_card"
                                                                    name="pos_payment_card" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_payment_card"
                                                                    name="pos_payment_card">
                                                            @endif
                                                            <label for="pos_payment_card" class="padding05">Tarjeta (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_payment_cash', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_payment_cash"
                                                                    name="pos_payment_cash" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_payment_cash"
                                                                    name="pos_payment_cash">
                                                            @endif
                                                            <label for="pos_payment_cash" class="padding05">Efectivo (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_payment_qrcash', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_payment_qrcash"
                                                                    name="pos_payment_qrcash" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_payment_qrcash"
                                                                    name="pos_payment_qrcash">
                                                            @endif
                                                            <label for="pos_payment_qrcash" class="padding05">Efectivo - QR
                                                                (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_create_due', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_create_due"
                                                                    name="pos_create_due" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_create_due"
                                                                    name="pos_create_due">
                                                            @endif
                                                            <label for="pos_create_due" class="padding05">Por Cobrar (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_payment_qr', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_payment_qr"
                                                                    name="pos_payment_qr" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_payment_qr"
                                                                    name="pos_payment_qr">
                                                            @endif
                                                            <label for="pos_payment_qr" class="padding05">Qr (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_payment_check', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_payment_check"
                                                                    name="pos_payment_check" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_payment_check"
                                                                    name="pos_payment_check">
                                                            @endif
                                                            <label for="pos_payment_check" class="padding05">Cheque (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_payment_giftcard', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_payment_giftcard"
                                                                    name="pos_payment_giftcard" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_payment_giftcard"
                                                                    name="pos_payment_giftcard">
                                                            @endif
                                                            <label for="pos_payment_giftcard" class="padding05">Gift Card
                                                                (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_payment_deposit', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_payment_deposit"
                                                                    name="pos_payment_deposit" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_payment_deposit"
                                                                    name="pos_payment_deposit">
                                                            @endif
                                                            <label for="pos_payment_deposit" class="padding05">Deposito
                                                                (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_paid_due', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_paid_due"
                                                                    name="pos_paid_due" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_paid_due"
                                                                    name="pos_paid_due">
                                                            @endif
                                                            <label for="pos_paid_due" class="padding05">Abonar Ventas
                                                                (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_recent_sales', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_recent_sales"
                                                                    name="pos_recent_sales" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_recent_sales"
                                                                    name="pos_recent_sales">
                                                            @endif
                                                            <label for="pos_recent_sales" class="padding05">Ventas Recientes
                                                                (Botón)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_discount_gral', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_discount_gral"
                                                                    name="pos_discount_gral" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_discount_gral"
                                                                    name="pos_discount_gral">
                                                            @endif
                                                            <label for="pos_discount_gral" class="padding05">Desactivar
                                                                Descuento General (Función)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_discount_item', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_discount_item"
                                                                    name="pos_discount_item" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_discount_item"
                                                                    name="pos_discount_item">
                                                            @endif
                                                            <label for="pos_discount_item" class="padding05">Limitar
                                                                Descuento Lineal (Función)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                                <span id="feature_customer_pos">
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('pos_customer_advanced', $all_permission))
                                                                <input type="checkbox" value="1" id="pos_customer_advanced"
                                                                    name="pos_customer_advanced" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="pos_customer_advanced"
                                                                    name="pos_customer_advanced">
                                                            @endif
                                                            <label for="pos_customer_advanced" class="padding05">Datos
                                                                Adicionales Cliente (Crear Cliente)
                                                                &nbsp;&nbsp;</label>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>


                                        <tr id="transfers-request">
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue">
                                                    <div class="checkbox">
                                                        @if (in_array('transfers-request', $blocked_modules))
                                                            <input type="checkbox" id="transfers-request-blocked"
                                                                name="blocked_modules[]" value="transfers-request" checked />
                                                        @else
                                                            <input type="checkbox" id="transfers-request-blocked"
                                                                name="blocked_modules[]" value="transfers-request" />
                                                        @endif
                                                        <label for="transfers-request-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Tranferencias</td>
                                            <td class="siat-permissions" colspan="4">
                                                <span>
                                                    <div aria-checked="false" aria-disabled="false">
                                                        <div class="checkbox">
                                                            @if (in_array('accept-transfers', $all_permission))
                                                                <input type="checkbox" value="1" id="accept-transfers"
                                                                    name="accept-transfers" checked>
                                                            @else
                                                                <input type="checkbox" value="1" id="accept-transfers"
                                                                    name="accept-transfers">
                                                            @endif
                                                            <label for="accept-transfers" class="padding05">Aceptar
                                                                transferencias</label>

                                                        </div>
                                                </span>

                                            </td>
                                        </tr>

                                        <!-- Módulos solo con bloqueo -->
                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue">
                                                    <div class="checkbox">
                                                        @if (in_array('dashboard', $blocked_modules))
                                                            <input type="checkbox" id="dashboard-blocked"
                                                                name="blocked_modules[]" value="dashboard" checked />
                                                        @else
                                                            <input type="checkbox" id="dashboard-blocked"
                                                                name="blocked_modules[]" value="dashboard" />
                                                        @endif
                                                        <label for="dashboard-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Tablero (Dashboard)</td>
                                            <td class="text-center" colspan="4">
                                                <span class="badge badge-secondary">Solo bloqueo</span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue">
                                                    <div class="checkbox">
                                                        @if (in_array('people', $blocked_modules))
                                                            <input type="checkbox" id="people-blocked" name="blocked_modules[]"
                                                                value="people" checked />
                                                        @else
                                                            <input type="checkbox" id="people-blocked" name="blocked_modules[]"
                                                                value="people" />
                                                        @endif
                                                        <label for="people-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Gente </td>
                                            <td class="text-center" colspan="4">
                                                <span class="badge badge-secondary">Solo bloqueo</span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue">
                                                    <div class="checkbox">
                                                        @if (in_array('proforma', $blocked_modules))
                                                            <input type="checkbox" id="proforma-blocked"
                                                                name="blocked_modules[]" value="proforma" checked />
                                                        @else
                                                            <input type="checkbox" id="proforma-blocked"
                                                                name="blocked_modules[]" value="proforma" />
                                                        @endif
                                                        <label for="proforma-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Proforma</td>
                                            <td class="text-center" colspan="4">
                                                <span class="badge badge-secondary">Solo bloqueo</span>
                                            </td>
                                        </tr>

                                        <!-- Reservations row (placed at the end) -->
                                        <tr class="{{ in_array('reservations', $blocked_modules) ? 'blocked-row' : '' }}">
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('reservations', $blocked_modules))
                                                            <input type="checkbox" id="reservations-blocked"
                                                                name="blocked_modules[]" value="reservations" checked />
                                                        @else
                                                            <input type="checkbox" id="reservations-blocked"
                                                                name="blocked_modules[]" value="reservations" />
                                                        @endif
                                                        <label for="reservations-blocked"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Reservas</td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('reservations-index', $all_permission))
                                                            <input type="checkbox" value="1" id="reservations-index"
                                                                name="reservations-index" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="reservations-index"
                                                                name="reservations-index">
                                                        @endif
                                                        <label for="reservations-index"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('reservations-add', $all_permission))
                                                            <input type="checkbox" value="1" id="reservations-add"
                                                                name="reservations-add" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="reservations-add"
                                                                name="reservations-add">
                                                        @endif
                                                        <label for="reservations-add"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('reservations-edit', $all_permission))
                                                            <input type="checkbox" value="1" id="reservations-edit"
                                                                name="reservations-edit" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="reservations-edit"
                                                                name="reservations-edit">
                                                        @endif
                                                        <label for="reservations-edit"></label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="icheckbox_square-blue" aria-checked="false"
                                                    aria-disabled="false">
                                                    <div class="checkbox">
                                                        @if (in_array('reservations-delete', $all_permission))
                                                            <input type="checkbox" value="1" id="reservations-delete"
                                                                name="reservations-delete" checked>
                                                        @else
                                                            <input type="checkbox" value="1" id="reservations-delete"
                                                                name="reservations-delete">
                                                        @endif
                                                        <label for="reservations-delete"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        function applyBlockedStyles() {
                                            document.querySelectorAll('.permission-table tbody tr').forEach(function (tr) {
                                                tr.classList.remove('blocked-row');
                                                var chk = tr.querySelector('input[name="blocked_modules[]"]');
                                                var isBlocked = !!(chk && chk.checked);
                                                if (isBlocked) {
                                                    tr.classList.add('blocked-row');
                                                }
                                                // Enable/disable all permission checkboxes in this row except the blocker
                                                tr.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
                                                    if (input.name === 'blocked_modules[]') return;
                                                    input.disabled = isBlocked;
                                                });
                                            });
                                        }
                                        applyBlockedStyles();
                                        document.querySelectorAll('input[name="blocked_modules[]"]').forEach(function (chk) {
                                            chk.addEventListener('change', applyBlockedStyles);
                                        });
                                    });
                                </script>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript">
        $("ul#setting").siblings('a').attr('aria-expanded', 'true');
        $("ul#setting").addClass("show");
        $("ul#setting #role-menu").addClass("active");

        if ($("#customers-add").val() == 1) {
            $("#feature_customer_pos").show();
        } else {
            $("#feature_customer_pos").hide();
        }

        $("#select_all").on("change", function () {
            if ($(this).is(':checked')) {
                $("tbody input[type='checkbox']").prop('checked', true);
            } else {
                $("tbody input[type='checkbox']").prop('checked', false);
            }
        });

        $("#module_siat").on("change", function () {
            if ($(this).is(':checked')) {
                $("#panel-siat").show();
            } else {
                $("#panel-siat").hide();
            }
        });



        $("#customers-add").on("change", function () {
            if ($(this).is(':checked')) {
                $("#feature_customer_pos").show();
            } else {
                $("#feature_customer_pos").hide();
            }
        });
    </script>
@endsection