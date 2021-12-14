jQuery(document).ready(function($) {

	$(document).on('click', ".related-post-meta .post-list .item .remove", function() {

		$(this).parent().remove();

	})

		$(document).on('click', ".suggest-post-list .item", function() {
			post_id = $(this).attr('post_id');
			post_title = $(this).attr('post_title');

			html = '<div class="item"><span class="remove"><i class="fas fa-times"></i></span> <span class="move"><i class="fas fa-sort"></i></span> <span class="title">'+post_title+'</span></div><input type="hidden" name="related_post_ids[]" value="'+post_id+'" /></div>';
			
			$('.related-post-meta .post-list').append(html);
		})

		$(document).on('keyup', ".related-post-meta .related_post_get_ids", function() {
			post_id = $(this).attr('post_id');
			title = $(this).val();
			any_posttypes = $('#any_posttypes:checked').val();

			console.log(any_posttypes);


			$.ajax({
				type: 'POST',
				context: this,
				url:related_post_ajax.related_post_ajaxurl,
				data: {
					"action"	: "related_post_ajax_get_post_ids",
					"title"		: title,
					"post_id"	: post_id,
					"any_posttypes"	: any_posttypes,

				},
				success: function(data) {
					var response = JSON.parse(data)
					html = response['html'];
					$('.suggest-post-list').html(html);
				}
			});
		})
});







