<div id="search-results-facebook" hidden>
    <div class="page-header text-center">
        <div class="row">
            <br><br>
            <p>
                <img class="social-media-logo" src="{{ asset('images/logos/Facebook-Logo-2.png') }}">
            </p>
            <small class="text-gray">
                Pages found for
                <em><span class="keyword-text"></span></em>
            </small>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="col-xs-2">
                    <label><input type="checkbox" id="check-all"/>Check all</label>
                </div>
                <div class="col-xs-2 text-uppercase text-large">Page</div>
                <div class="col-xs-8 text-center text-uppercase text-large">Description</div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            @for ($i = 0; $i < 10; $i++)
                <p class="col-xs-12" hidden>
                    <div class="col-xs-4">
                        <input
                                id="facebook-result-{{$i+1}}"
                                type="checkbox"
                        >
                    </div>
                    <div class="col-xs-8">
                        <span id="facebook-description-{{$i+1}}"></span>
                    </div>
                </p>
            @endfor
        </div>
        <div class="row text-center">
            <button id="collect-facebook" class="btn btn-primary btn-lg">
                Collect <i class="fa fa-facebook-official" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>