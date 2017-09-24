<div class="col-xs-4 text-center">
    <p>
        <img class="social-media-logo" src="{{ asset('images/logos/logo_twitter.png') }}">
    </p>
    <p>
        <label>Number of Tweets</label>
        <input
                id="number-of-tweets"
                class="text-center"
                type="number"
        >
        <br>
        <small class="text-gray">By default 100</small>
    </p>
    <p>
        <button id="collect-twitter" class="btn btn-primary btn-lg start-searching">
            Collect <i class="fa fa-twitter" aria-hidden="true"></i>
        </button>
    </p>
    <p class="alert alert-success" id="twitter-alert-success" style="display:none">
        Data collected! <br>
        <button class="btn btn-default">
            <i class="fa fa-download" aria-hidden="true"></i>
            <span id="twitter-download-link"></span>
        </button>
    </p>
    <p class="alert alert-danger" id="twitter-alert-failure" style="display:none">
        Data collection failed! <br>
        <span id="twitter-error-message"></span>
    </p>
</div>