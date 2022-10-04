<?php

namespace App\DataTables\Users;

use App\Models\User;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     *
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            //->of(User::select('*'))
            ->eloquent($query)
            ->editColumn('id', function (User $model) {
                return $model->id;
            })
            ->editColumn('first_name', function (User $model) {
                if (!isset($model->name)) {
                    return '';
                }
                return $model->name;
            })
            ->editColumn('info.company', function (User $model) {
                if (!isset($model->info->company)) {
                    return '';
                }
                return $model->info->company;
            })
            ->editColumn('info.phone', function (User $model) {
                if (!isset($model->info->phone)) {
                    return '';
                }
                return $model->info->phone;
            })
            ->editColumn('info.website', function (User $model) {
                if (!isset($model->info->website)) {
                    return '';
                }
                return $model->info->website;
            })
            ->editColumn('info.country', function (User $model) {
                if (!isset($model->info->country)) {
                    return '-';
                }
                foreach(\App\Core\Data::getCountriesList() as $key => $value) {
                    if ($key === $model->info->country) {
                        return $value['name'];
                    }
                }
            })
            ->editColumn('info.language', function (User $model) {
                if (!isset($model->info->language)) {
                    return '';
                }
                foreach(\App\Core\Data::getLanguagesList() as $key => $value) {
                    if ($key === $model->info->language) {
                        return $value['name'];
                    }
                }
            })
            ->editColumn('info.timezone', function (User $model) {
                if (!isset($model->info->timezone)) {
                    return '';
                }
                return $model->info->timezone;
            })
            ->editColumn('info.currency', function (User $model) {
                if (!isset($model->info->currency)) {
                    return '';
                }
                foreach(\App\Core\Data::getCurrencyList() as $key => $value) {
                    if ($key === $model->info->currency) {
                        return $value['name'];
                    }
                }
            })
            ->editColumn('info.communication', function (User $model) {
                if (!isset($model->info->communication)) {
                    return '';
                }
                $test = implode(', ', array_map('ucwords', array_keys(array_filter((array)$model->info->communication ?? []) ?? [])));
                return $test;
            })
            ->editColumn('info.marketing', function (User $model) {
                if (!isset($model->info->marketing)) {
                    return '-';
                }
                return $model->info->marketing === 1 ? "Yes" : "-";
            })
            ->editColumn('created_at', function (User $model) {
                return $model->created_at->format('d M, Y H:i:s');
            })
            ->addColumn('action', function (User $model) {
                return view('pages.users._action-menu', compact('model'));
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param  Activity  $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        return $model->newQuery()->with('info');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->stateSave(true)
            ->orderBy(0, "asc")
            ->responsive()
            ->autoWidth(false)
            ->parameters([
                'scrollX'      => true,
                'ordering'     => true,
                'drawCallback' => 'function() { KTMenu.createInstances(); }',
            ])
            // ->buttons(
            //     [
            //         [
            //             'text' =>'<i class="fa fa-eye"></i> ' . 'My custom button',
            //             'className' => 'My custom class'
            //         ],
            //          'csv',
            //          'excel'
            //     ],
            // )
            ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('first_name')->title('Name'),
            Column::make('info.phone')->title('Phone'),
            Column::make('company')->title('Company')->data('info.company')->name('info.company'),
            Column::make('info.country')->title('Country'),
            Column::make('created_at')->title('Created At'),
            Column::make('info.website')->title('Website')->addClass('none'),
            Column::make('info.language')->title('Language')->addClass('none'),
            Column::make('info.timezone')->title('Timezone')->addClass('none'),
            Column::make('info.currency')->title('Currency')->addClass('none'),
            Column::make('info.communication')->title('Communication')->addClass('none'),
            Column::make('info.marketing')->title('Marketing')->addClass('none'),
            Column::computed('action')
                ->exportable(true)
                ->printable(false)
                ->addClass('text-center')
                ->responsivePriority(-1)
                ->addClass('d-flex'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'users_'.date('YmdHis');
    }
}
