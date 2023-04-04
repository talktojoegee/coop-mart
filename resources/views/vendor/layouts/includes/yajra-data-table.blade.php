<link rel="stylesheet" href="{{ asset('dist/plugins/Responsive-2.2.5/css/responsive.dataTables.min.css') }}">
  <!-- daterange picker -->
<link rel="stylesheet" href="{{ asset('dist/plugins/bootstrap-daterangepicker/daterangepicker.min.css') }}">

<div class="table-responsive pt-2">
    {!! $dataTable->table(['class' => 'table table-striped table-hover dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
</div>

<script src="{{ asset('dist/plugins/DataTables-1.10.21/js/jquery.dataTablesCus.min.js') }}"></script>
<script src="{{ asset('dist/plugins/Responsive-2.2.5/js/dataTables.responsive.min.js') }}"></script>

{!! $dataTable->scripts() !!}

<script src="{{ asset('dist/js/custom/yajra-custom.min.js') }}"></script>
