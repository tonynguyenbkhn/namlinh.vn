<?php

$data = wp_parse_args($args, [
	'class' => '',
	'image' => '',
]);

if (!empty($data)) : ?>
  <div class="testimonial-card">
    <?php echo twmp_get_svg_icon('quotation-marks-open') ?>
    <blockquote class="wysiwyg testimonial-card__comment">
      <div class="testimonial-card__inner">
        <?php if (!empty($data['title'])) : ?>
          <p class="testimonial-card__title"><?php echo $data['title']; ?></p>
        <?php endif; ?>
        <?php echo $data['description']; ?>
      </div>
      <figure class="testimonial-card__author">
            <?php 
                get_template_part('templates/core-blocks/image', null, [
                    'image_id' => $data['image_id'],
                    'image_size' => 'full',
                    'lazyload' => false,
                    'class' => 'pe-none image--cover logo-slider-item__image image--default',
                ]);
            ?>
        </figure>
        <footer class="testimonial-card__author-info">
            <p><?php echo $data['name'] ?></p>
            <cite><?php echo $data['profession'] ?></cite>
    </blockquote>
    <?php echo twmp_get_svg_icon('quotation-marks-close') ?>
  </div>
<?php endif; ?>
