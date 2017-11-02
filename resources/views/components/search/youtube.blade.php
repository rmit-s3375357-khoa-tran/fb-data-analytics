<div class="col-xs-4 text-center">
    <p>
        <img class="social-media-logo" src="{{ asset('images/logos/youtube_2017_logo.png') }}">
    </p>
    <p>
        <label>Number of Videos</label>
        <input
                id="number-of-youtube-videos"
                class="text-center"
                type="number"
        >
        <br>
        <small class="text-gray">By default 3, max 10</small>
    </p>
    <p>
        <button id="search-youtube" class="btn btn-primary btn-lg start-searching">
            Search <i class="fa fa-youtube" aria-hidden="true"></i>
        </button>
    </p>
    <p class="alert alert-success" id="youtube-alert-success" style="display:none">
        Data collected! <br>
        <a id="youtube-download-link" class="btn btn-default">
            <i class="fa fa-download" aria-hidden="true"></i> Download
        </a>
    </p>
    <p class="alert alert-danger" id="youtube-alert-failure" style="display:none">
        <u>Data collection failed! </u><br>
        <span id="youtube-error-message"></span>
    </p>
</div>