@extends('layouts.master')

@section('title', 'Mail Sender')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/js/plugin/sweetalert/sweetalert2.min.css') }}">
    <style>
        .dropzone {
            border: 2px dashed #007bff;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .dropzone .dz-message {
            font-weight: 400;
        }

        .dropzone .dz-message .note {
            font-size: 0.8em;
            font-weight: 200;
            display: block;
            margin-top: 1.4rem;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h4 class="page-title">Utilities</h4>
                <ul class="breadcrumbs">
                    <li class="nav-home">
                        <a href="#">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Utilities</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Mail Sender</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Send Email</div>
                        </div>
                        <div class="card-body">
                            <form id="mailForm" action="{{ route('utilities.mail-sender.send') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group @error('email') has-error @enderror">
                                    <label for="email">Recipient Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter recipient email" value="{{ old('email') }}" required>
                                    <span class="text-danger error-text email_error"></span>
                                </div>
                                <div class="form-group @error('subject') has-error @enderror">
                                    <label for="subject">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject"
                                        placeholder="Enter subject" value="{{ old('subject') }}" required>
                                    <span class="text-danger error-text subject_error"></span>
                                </div>
                                <div class="form-group @error('message') has-error @enderror">
                                    <label for="message">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" placeholder="Enter your message" required>{{ old('message') }}</textarea>
                                    <span class="text-danger error-text message_error"></span>
                                </div>

                                <div class="form-group">
                                    <label>Attachments</label>
                                    <div class="dropzone" id="document-dropzone">
                                        <div class="dz-message" data-dz-message>
                                            <span>Drag & Drop files here or click to upload</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-action">
                                    <button type="submit" id="submitBtn" class="btn btn-success">Send Email</button>
                                    <button type="reset" class="btn btn-danger">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/dropzone/dropzone.min.js') }}"></script>
    <script>
        Dropzone.autoDiscover = false;

        $(document).ready(function() {
            var myDropzone = new Dropzone("#document-dropzone", {
                url: "{{ route('utilities.mail-sender.send') }}",
                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 10,
                maxFiles: 10,
                addRemoveLinks: true,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                init: function() {
                    var dz = this;
                    var submitButton = document.querySelector("#submitBtn");

                    submitButton.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        if (dz.getQueuedFiles().length > 0) {
                            dz.processQueue();
                        } else {
                            // If no files, stick to normal form submission (via AJAX to handle consistent response)
                            var formData = new FormData(document.getElementById("mailForm"));

                            $.ajax({
                                url: "{{ route('utilities.mail-sender.send') }}",
                                type: "POST",
                                data: formData,
                                processData: false,
                                contentType: false,
                                beforeSend: function() {
                                    $(submitButton).attr('disabled', true).html(
                                        '<i class="fas fa-spinner fa-spin"></i> Sending...'
                                    );
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message
                                    }).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    $(submitButton).attr('disabled', false).text(
                                        'Send Email');
                                    var errorMessage = xhr.responseJSON.message ||
                                        'Something went wrong!';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: errorMessage
                                    });
                                }
                            });
                        }
                    });

                    this.on("sendingmultiple", function(data, xhr, formData) {
                        formData.append("email", $("#email").val());
                        formData.append("subject", $("#subject").val());
                        formData.append("message", $("#message").val());

                        $(submitButton).attr('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> Sending...');
                    });

                    this.on("successmultiple", function(files, response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(() => {
                            location.reload();
                        });
                    });

                    this.on("errormultiple", function(files, response) {
                        $(submitButton).attr('disabled', false).text('Send Email');
                        var errorMessage = response.message || 'Upload failed';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    });
                }
            });
        });
    </script>
@endsection
