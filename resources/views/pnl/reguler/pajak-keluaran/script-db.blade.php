@section('script')
    <script src="{{ asset('assets/js/plugin/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
    @include('pnl.reguler.pajak-keluaran.pkp-script-db')
    @include('pnl.reguler.pajak-keluaran.pkpnppn-script-db')
    @include('pnl.reguler.pajak-keluaran.npkp-script-db')
    @include('pnl.reguler.pajak-keluaran.npkpnppn-script-db')
    @include('pnl.reguler.pajak-keluaran.retur-script-db')
    @include('pnl.reguler.pajak-keluaran.nonstandar-script-db')
    @include('pnl.reguler.pajak-keluaran.pembatalan-script-db')
    @include('pnl.reguler.pajak-keluaran.koreksi-script-db')
    @include('pnl.reguler.pajak-keluaran.pending-script-db')
    @include('pnl.reguler.pajak-keluaran.main-script-db')
    @include('pnl.reguler.pajak-keluaran.download-script-db')
@endsection
