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
                    <label><input type="checkbox" class="check-all">Check all</label>
                </div>
                <div class="col-xs-2 text-uppercase text-large">Title</div>
                <div class="col-xs-2 text-uppercase text-large">Publish At</div>
                <div class="col-xs-6 text-center text-uppercase text-large">Description</div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            @for ($i = 0; $i < 13; $i++)
                <div id="youtube-row-{{$i}}" class="col-xs-12" hidden>
                    <p>
                        <div class="col-xs-4">
                            <input
                                    id="youtube-id-{{$i}}"
                                    type="checkbox"
                                    class="youtube-video-id"
                                    @if($i>9) checked @endif
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
                </div>
            @endfor
        </div>
        <div id="youtube-url-group" class="row">
            <div class="col-xs-12">
                <span class="text-uppercase text-large">Add Your Own URLs</span>
                <small class="text-gray">Separate URLs by comma, max 3, invalid url would be ignored</small><br>
                <button id="add-youtube-urls" class="btn btn-primary btn-lg">
                    <i class="fa fa-plus" aria-hidden="true"></i> Add
                </button>
                <input
                        id="youtube-custom-urls"
                        type="text"
                        style="width: 70%"
                >
            </div>
            <div class="col-xs-12" id="youtube-add-url-failure" hidden>
                <div class="col-xs-3"></div>
                <div class="col-xs-4 text-center">
                    <p class="alert alert-danger">
                        <span id="youtube-add-url-error"></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="row text-center">
            <div>
                <label>Number of Max Comments</label>
                <input
                        id="youtube-count"
                        class="text-center"
                        type="number"
                ><br>
                <small class="text-gray">By default 100</small>
            </div>
            <button id="collect-youtube" class="btn btn-primary btn-lg">
                Collect <i class="fa fa-youtube" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>