<div class="col-xs-4 text-center">
    <p>
        <img class="social-media-logo" src="{{ asset('images/logos/Facebook-Logo-2.png') }}">
    </p>
    <p>
        <label>Number of Pages</label>
        <input
                id="number-of-facebook-pages"
                class="text-center"
                type="number"
        >
        <br>
        <small class="text-gray">By default 3, max 10</small>
    </p>
    <p>
        <button id="search-facebook" class="btn btn-primary btn-lg start-searching">
            Search <i class="fa fa-facebook-official" aria-hidden="true"></i>
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