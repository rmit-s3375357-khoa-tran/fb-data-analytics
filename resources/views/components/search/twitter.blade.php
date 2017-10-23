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
        <small class="text-gray">By default 100</small><br>
    </p>
    <p>
        <div>
            <button id="stream-twitter" class="btn btn-primary btn-lg start-searching">
                Stream <i class="fa fa-twitter" aria-hidden="true"></i>
            </button>
            <button id="collect-twitter" class="btn btn-primary btn-lg start-searching">
                Collect <i class="fa fa-twitter" aria-hidden="true"></i>
            </button>
        </div>
        <div id="twitter-explanation">
            <small class="text-gray">Streaming might take a while</small><br>
            <small class="text-gray">Max for Collecting is 100</small>
        </div>
    </p>
    <p class="alert alert-success" id="twitter-alert-success" style="display:none">
        Data collected! <br>
        <a id="twitter-download-link" class="btn btn-default">
            <i class="fa fa-download" aria-hidden="true"></i> Download
        </a>
    </p>
    <p class="alert alert-danger" id="twitter-alert-failure" style="display:none">
        <u>Data collection failed! </u><br>
        <span id="twitter-error-message"></span>
    </p>
</div>