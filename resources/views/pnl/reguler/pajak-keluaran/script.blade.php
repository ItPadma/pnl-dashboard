@section('script')
    <script src="{{ asset('assets/js/plugin/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
    @include('pnl.reguler.pajak-keluaran.pkp-script')
    @include('pnl.reguler.pajak-keluaran.pkpnppn-script')
    @include('pnl.reguler.pajak-keluaran.npkp-script')
    @include('pnl.reguler.pajak-keluaran.npkpnppn-script')
    @include('pnl.reguler.pajak-keluaran.retur-script')
    @include('pnl.reguler.pajak-keluaran.nonstandar-script')
    @include('pnl.reguler.pajak-keluaran.pembatalan-script')
    @include('pnl.reguler.pajak-keluaran.koreksi-script')
    @include('pnl.reguler.pajak-keluaran.pending-script')
    @include('pnl.reguler.pajak-keluaran.main-script')
    @include('pnl.reguler.pajak-keluaran.download-script')
@endsection
