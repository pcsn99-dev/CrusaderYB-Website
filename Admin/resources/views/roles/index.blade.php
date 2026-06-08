<x-app-layout>

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="h5 mb-0 fw-semibold text-dark">
                Role Management
            </h2>
        </div>
    </x-slot>

    <div class="card-header">
        <h3 class="card-title">Users</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 16rem">
                <span class="input-group-text">
                    <i class="bi bi-search" aria-hidden="true"></i>
                </span>
                <input id="table-filter" type="search" class="form-control" placeholder="Filter rows…" aria-label="Filter rows">
                </div>
            </div>
    </div>
    <div class="card-body">
    <div class="d-flex gap-2 mb-3">
        <button id="export-csv" type="button" class="btn btn-sm btn-dark">
            <i class="bi bi-plus-circle" aria-hidden="true"></i>
            Create Role
        </button>
        
    </div>
    <div id="users-table" class="tabulator" role="grid" aria-owns="tabulator-table-body" tabulator-layout="fitColumns">

    <div class="tabulator-header" role="rowgroup">
        <div class="tabulator-header-contents">

            <div class="tabulator-headers" role="row" style="height: 84px;">

                <span class="tabulator-col-resize-handle" style="height: 84px;"></span>

                <div class="tabulator-col tabulator-sortable tabulator-col-sorter-element"
                     role="columnheader" aria-sort="none"
                     tabulator-field="name"
                     style="min-width: 40px; width: 338px; height: 84px;">

                    <div class="tabulator-col-content">
                        <div class="tabulator-col-title-holder">
                            <div class="tabulator-col-title">Name</div>
                            <div class="tabulator-col-sorter">
                                <div class="tabulator-arrow"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <span class="tabulator-col-resize-handle"></span>

                <div class="tabulator-col tabulator-sortable tabulator-col-sorter-element"
                     role="columnheader" tabulator-field="email"
                     style="min-width: 40px; width: 338px; height: 84px;">

                    <div class="tabulator-col-content">
                        <div class="tabulator-col-title-holder">
                            <div class="tabulator-col-title">Email</div>
                        </div>

                        <div class="tabulator-header-filter">
                            <input type="search">
                        </div>
                    </div>
                </div>

                <span class="tabulator-col-resize-handle"></span>

                <div class="tabulator-col tabulator-sortable tabulator-col-sorter-element"
                     role="columnheader" tabulator-field="role"
                     style="min-width: 40px; width: 120px; height: 84px;">

                    <div class="tabulator-col-content">
                        <div class="tabulator-col-title-holder">
                            <div class="tabulator-col-title">Role</div>
                        </div>
                    </div>
                </div>

                <span class="tabulator-col-resize-handle"></span>

                <div class="tabulator-col tabulator-sortable tabulator-col-sorter-element"
                     role="columnheader" tabulator-field="status"
                     style="min-width: 40px; width: 130px; height: 84px;">

                    <div class="tabulator-col-content">
                        <div class="tabulator-col-title-holder">
                            <div class="tabulator-col-title">Status</div>
                        </div>
                    </div>
                </div>

                <span class="tabulator-col-resize-handle"></span>

                <div class="tabulator-col tabulator-sortable tabulator-col-sorter-element"
                     role="columnheader" tabulator-field="joined"
                     style="min-width: 40px; width: 130px; height: 84px;">

                    <div class="tabulator-col-content">
                        <div class="tabulator-col-title-holder">
                            <div class="tabulator-col-title">Joined</div>
                        </div>
                    </div>
                </div>

                <span class="tabulator-col-resize-handle"></span>

            </div>

            <div class="tabulator-frozen-rows-holder"></div>

        </div>
    </div>

    <div class="tabulator-tableholder" tabindex="0" style="height: 490px;">

        <div class="tabulator-table" role="rowgroup" id="tabulator-table-body">

            <div class="tabulator-row tabulator-selectable tabulator-row-odd" role="row">

                <div class="tabulator-cell" role="gridcell">Olivia Bennett</div>
                <span class="tabulator-col-resize-handle"></span>

                <div class="tabulator-cell" role="gridcell">olivia@example.com</div>
                <span class="tabulator-col-resize-handle"></span>

                <div class="tabulator-cell" role="gridcell">Admin</div>
                <span class="tabulator-col-resize-handle"></span>

                <div class="tabulator-cell" role="gridcell">
                    <span class="badge text-bg-success">Active</span>
                </div>
                <span class="tabulator-col-resize-handle"></span>

                <div class="tabulator-cell" role="gridcell">2024-03-12</div>
                <span class="tabulator-col-resize-handle"></span>

            </div>

        </div>
        <div class="tabulator-footer-contents"><span class="tabulator-paginator"><label>Page Size</label><select class="tabulator-page-size" aria-label="Page Size" title="Page Size"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><button class="tabulator-page" type="button" role="button" aria-label="First Page" title="First Page" data-page="first" disabled="">First</button><button class="tabulator-page" type="button" role="button" aria-label="Prev Page" title="Prev Page" data-page="prev" disabled="">Prev</button><span class="tabulator-pages" style=""><button class="tabulator-page active" type="button" role="button" aria-label="Show Page 1" title="Show Page 1" data-page="1">1</button><button class="tabulator-page" type="button" role="button" aria-label="Show Page 2" title="Show Page 2" data-page="2">2</button></span><button class="tabulator-page" type="button" role="button" aria-label="Next Page" title="Next Page" data-page="next">Next</button><button class="tabulator-page" type="button" role="button" aria-label="Last Page" title="Last Page" data-page="last">Last</button></span></div>

    </div>



</x-app-layout>
<script>
      const statusBadge = (cell) => {
        const value = cell.getValue();
        const map = { Active: 'success', Invited: 'info', Suspended: 'secondary' };
        const color = map[value] || 'secondary';
        return `<span class="badge text-bg-${color}">${value}</span>`;
      };

      document.addEventListener('DOMContentLoaded', () => {

        const data = [
           @foreach ($roles as $role)
            {
                name: @json($role->name),
                slug: @json($role->slug),
                permissions_count: {{ $role->permissions_count ?? 0 }},
                users_count: {{ $role->users_count ?? 0 }},
            },
            @endforeach

        ];

        const table = new Tabulator("#users-table", {
            data: data,
            layout: "fitColumns",
           
            pagination: true,
            paginationMode: "local",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50],

            columns: [
                { title: "Name", field: "name" },

                { title: "Slug", field: "slug" },

                { title: "Permissions", field: "permissions_count", hozAlign: "center" },

                { title: "Users", field: "users_count", hozAlign: "center" },

                {
                    title: "Actions",
                    field: "id",
                    formatter: function (cell) {
                        const id = cell.getValue();

                        return `
                            <div class="d-flex gap-1">
                                <a href="/roles/${id}" class="btn btn-sm btn-primary">View</a>
                                <a href="/roles/${id}/edit" class="btn btn-sm btn-warning">Edit</a>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </div>
                        `;
                    }
                }
            ],
        });

        document.getElementById('table-filter').addEventListener('input', (e) => {
          const value = e.target.value;
          if (value) {
            table.setFilter([
              [
                { field: 'name', type: 'like', value: value },
                { field: 'email', type: 'like', value: value },
              ],
            ]);
          } else {
            table.clearFilter();
          }
        });

        document
          .getElementById('export-csv')
          .addEventListener('click', () => table.download('csv', 'users.csv'));
        document
          .getElementById('export-json')
          .addEventListener('click', () => table.download('json', 'users.json'));
        document
          .getElementById('print-table')
          .addEventListener('click', () => table.print(false, true));
      });
    </script>