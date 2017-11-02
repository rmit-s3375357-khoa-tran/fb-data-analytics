<div id="search-component">
    <div class="page-header text-center">
        <img src="<?php echo e(asset('images/logos/GrabSentiment.png')); ?>">
        <div class="text-uppercase text-large">
            Enter Your Keyword*:
            <input
                    id="keyword"
                    class="text-center disable-when-searching"
                    type="text"
            >
        </div>
        <div>
            <div class="text-uppercase text-large">
                Set Your Stop Words:
                <input
                        id="stop-words"
                        class="text-center disable-when-searching"
                        type="text"
                >
            </div>
            <small class="text-gray">Separate stop words by comma</small>
        </div>
        <div>
            <div class="text-uppercase text-large">
                Results Only After:
                <input
                        id="starting-date"
                        class="text-center disable-when-searching"
                        type="text"
                >
            </div>
            <small class="text-gray">By default set to a month ago</small>
        </div>
        <div class="text-right">
            <button id="reset-all" class="btn btn-default btn-lg">
                <i class="fa fa-refresh" aria-hidden="true"></i> Reset All
            </button>
        </div>
    </div>
    <div class="container">
        <div class="col-xs-12">
            <?php echo $__env->make('components.search.twitter', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            <?php echo $__env->make('components.search.facebook', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            <?php echo $__env->make('components.search.youtube', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
        </div>
        <div class="text-right">
            <a id="analyse" class="btn btn-primary btn-lg">
                <i class="fa fa-magic" aria-hidden="true"></i> Analyse
            </a>
        </div>
    </div>
</div>