<div id="search-results-component">
    <?php echo $__env->make('components.results.facebook', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php echo $__env->make('components.results.youtube', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
</div>