<div id="search-results-youtube" hidden>
    <div class="page-header text-center">
        <div class="row">
            <br><br>
            <p>
                <img class="social-media-logo" src="{{ asset('images/logos/youtube_2017_logo.png') }}">
            </p>
            <small class="text-gray">
                Videos found for
                <em><span class="keyword-text"></span></em>
            </small>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="col-xs-2">
                    <label><input type="checkbox" id="check-all">Check all</label>
                </div>
                <div class="col-xs-2 text-uppercase text-large">Title</div>
                <div class="col-xs-2 text-uppercase text-large">Publish At</div>
                <div class="col-xs-6 text-center text-uppercase text-large">Description</div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            @for ($i = 0; $i < 10; $i++)
                <p id="youtube-row-{{$i}}" class="col-xs-12" hidden>
                    <div class="col-xs-4">
                        <input
                                id="youtube-id-{{$i}}"
                                type="checkbox"
                        >
                        <span id="youtube-title-{{$i}}"></span>
                    </div>
                    <div class="col-xs-2">
                        <span id="youtube-publish-{{$i}}"></span>
                    </div>
                    <div class="col-xs-6">
                        <span id="youtube-description-{{$i}}"></span>
                    </div>
                </p>
            @endfor
        </div>
        <div class="row text-center">
            <button id="collect-youtube" class="btn btn-primary btn-lg">
                Collect <i class="fa fa-youtube" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>