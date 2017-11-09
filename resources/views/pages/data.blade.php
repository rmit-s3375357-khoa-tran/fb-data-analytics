<div>
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#twitter">Twitter</a></li>
        <li><a data-toggle="tab" href="#facebook">Facebook</a></li>
        <li><a data-toggle="tab" href="#youtube">Youtube</a></li>
    </ul>

    <div class="tab-content">
        <div id="twitter" class="tab-pane fade in active">
            @if($results)
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <h3>Twitter</h3>
                    <p>Select sentiment to view data</p>
                    <button id="twitter_pos_button" class="btn btn-primary">Positive sentiments</button>
                    <button id="twitter_neg_button" class="btn btn-danger">Negative sentiments</button>
                    <button id="twitter_neu_button" class="btn btn-default">Neutral sentiments</button>
                </div>
                <div class="col-md-3"></div>
            </div>
                <div class="panel-group" id="accordion">
                    <div class="panel">
                        <div class="panel-body">
                            <div id="twitterData"></div>
                        </div>
                    </div>
                </div>
            @else
               <div class="title"> Twitter analysis not available </div>
            @endif
        </div>

        <div id="facebook" class="tab-pane fade in">
            @if($FBresults)
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <h3>Facebook</h3>
                        <p>Select sentiment to view data</p>
                        <button id="facebook_pos_button" class="btn btn-primary">Positive sentiments</button>
                        <button id="facebook_neg_button" class="btn btn-danger">Negative sentiments</button>
                        <button id="facebook_neu_button" class="btn btn-default">Neutral sentiments</button>
                    </div>
                    <div class="col-md-3"></div>
                </div>
                <div class="panel-group" id="accordion">
                    <div class="panel">
                        <div class="panel-body">
                            <div id="facebookData"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="title"> Facebook analysis not available </div>
            @endif
        </div>

        <div id="youtube" class="tab-pane fade in">
            @if($YTresults)
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <h3>Youtube</h3>
                    <p>Select sentiment to view data</p>
                    <button id="youtube_pos_button" class="btn btn-primary">Positive sentiments</button>
                    <button id="youtube_neg_button" class="btn btn-danger">Negative sentiments</button>
                    <button id="youtube_neu_button" class="btn btn-default">Neutral sentiments</button>
                </div>
                <div class="col-md-3"></div>
            </div>
                <div class="panel-group" id="accordion">
                    <div class="panel">
                        <div class="panel-body">
                            <div id="youtubeData"></div>
                        </div>
                    </div>
                </div>
                    @else
                        <div class="title"> Youtube analysis not available </div>
                    @endif
        </div>
    </div>
</div>