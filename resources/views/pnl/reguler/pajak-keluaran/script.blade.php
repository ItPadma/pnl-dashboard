@section('script')
    <script src="{{ asset('assets/js/plugin/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
    <script>
        let tablePkp;
        let tablePkpNppn;
        let tableNonPkp;
        let tableNonPkpNppn;
        let tableRetur;

        $.fn.dataTable.ext.errMode = 'none';

        let pkp_data = [];
        let pkpnppn_data = [];
        let npkp_data = [];
        let npkpnppn_data = [];
        let retur_data = [];
    </script>
    @include('pnl.reguler.pajak-keluaran.pkp-script')
    @include('pnl.reguler.pajak-keluaran.pkpnppn-script')
    @include('pnl.reguler.pajak-keluaran.npkp-script')
    @include('pnl.reguler.pajak-keluaran.npkpnppn-script')
    @include('pnl.reguler.pajak-keluaran.retur-script')
    @include('pnl.reguler.pajak-keluaran.main-script')
    @include('pnl.reguler.pajak-keluaran.download-script')
@endsection
