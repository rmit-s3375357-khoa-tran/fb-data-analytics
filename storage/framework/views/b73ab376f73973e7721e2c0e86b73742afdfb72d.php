<?php $__env->startSection('content'); ?>
    <div class="container">
        <input id="_token" value="<?php echo e(csrf_token()); ?>" hidden>
        <?php echo $__env->make('components.search.search', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
        <?php echo $__env->make('components.results.results', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('js/search.js')); ?>"></script>
    <script src="<?php echo e(asset('js/twitter.js')); ?>"></script>
    <script src="<?php echo e(asset('js/youtube.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('pages.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>