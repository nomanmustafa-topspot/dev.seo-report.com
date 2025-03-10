@extends('backend.layouts.main')
@section('title', 'Add Client')
@section('content')

    <main id="main" class="main">
        <div class="pagetitle">
        </div><!-- End Page Title -->
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">

                        @if (session('success'))
                            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show"
                                role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show"
                                role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">
                                @if (isset($client))
                                    Udpate client
                                @else
                                    Add New client
                                @endif
                            </h5>

                            <!-- No Labels Form -->
                            <form method="post" class="row g-3"
                                action="{{ isset($client) ? URL::to('client-update') : URL::to('/save-client') }} "
                                id="createclient">
                                @csrf
                                @if (isset($client))
                                    <input type="hidden" name="client_id" value="{{ $client->id }} " />
                                @endif

                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Name" id="name"
                                        name="name" value="{{ isset($client) ? $client->name : '' }}">
                                </div>

                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Email" id="email"
                                        name="email" value="{{ isset($client) ? $client->email : '' }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Designation" id="designation"
                                        name="designation" value="{{ isset($client) ? $client->designation : '' }}">
                                </div>

                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Website" name="website"
                                        value="{{ isset($client) ? $client->website : '' }}">
                                </div>

                                <div class="col-md-6">
                                    <input type="date" class="form-control" placeholder="date" name="date"
                                        value="{{ isset($client) ? $client->date : '' }}">
                                </div>

                                <div class="text-center">
                                    <button type="submit"
                                        class="btn btn-primary">{{ isset($client) ? 'Update' : 'Create' }}</button>
                                </div>
                            </form><!-- End No Labels Form -->

                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <!-- jQuery Validate -->
    <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.19.3/jquery.validate.min.js"></script>

    <script>
        $(document).ready(function() {
            // Add your form validation logic here

            $("#createclient").validate({
                rules: {
                    // Specify rules for your form fields
                    name: {
                        required: true,
                    },
                    email: {
                        required: true,
                    },
                    website: {
                        required: true,
                    },
                    designation: {
                        required: true,
                    },
                    date: {
                        required: true,
                    },
                },
                messages: {
                    // Specify custom error messages
                    website: {
                        required: "Website is required",
                    },
                    date: {
                        required: "Date is required",
                    },
                    designation: {
                        required: "Designation is required",
                    },
                    name: {
                        required: "Name is required",
                    },
                    email: {
                        required: "Email is required",
                    }
                },
                submitHandler: function(form) {
                    form.submit();
                }
            });
        });
    </script>
@endsection
