<div class="col-xs-4 text-center">
    <p>
        <img class="social-media-logo" src="{{ asset('images/logos/Facebook-Logo-2.png') }}">
    </p>
    <div class="search-field">
        <p>
            <label>Page ID</label>
            <input
                    id="facebook-page-id"
                    class="text-center"
                    type="text"
            >
        </p>
        <p>
            <label>Number of Comments</label>
            <input
                    id="number-of-facebook-comments"
                    class="text-center"
                    type="number"
            >
            <br>
            <small class="text-gray">By default 100</small>
        </p>
    </div>
    <p>
        <button id="collect-facebook" class="btn btn-primary btn-lg start-searching">
            Collect <i class="fa fa-facebook-official" aria-hidden="true"></i>
        </button>
    </p>
    <p class="alert alert-success" id="facebook-alert-success" style="display:none">
        Data collected! <br>
        <a id="facebook-download-link" class="btn btn-default">
            <i class="fa fa-download" aria-hidden="true"></i> Download
        </a>
    </p>
    <p class="alert alert-danger" id="facebook-alert-failure" style="display:none">
        <u>Data collection failed! </u><br>
        <span id="facebook-error-message"></span>
    </p>
</div>