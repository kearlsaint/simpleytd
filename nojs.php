








		<div hideme>
		<?php if(isset($data)) { ?>
			<a href="<?=$_SERVER['PHP_SELF']?>">&larr; NEW</a>
			<ul>
			<?php foreach($data as $item) { ?>
				<li>
					<span white><?=$item['title']?></span>
					<h6><?=$item['link']?></h6>
					<ul>
					<?php foreach($item['data'] as $link) { ?>
						<li>
							<a href="<?=$link['url']?>" download="<?=$item['title']?>.<?=$link['filetype']?>" target="_blank">
								<small><?=$link['type']?></small>
							</a>
						</li>
					<?php } ?>
					</ul>
				</li>
			<?php } ?>
			</ul>
		<?php	} else { ?>
			<p>Paste youtube links here. Separate with newlines/enterKey.<br><small red>(Lite version(No javascript). Max of 3 urls)</small></p>
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
				<textarea name="multi" placeholder="https://www.youtube.com/watch?v=xxxxxxxxxxx"></textarea>
				<input type="hidden" name="token" value="<?=$token?>">
				<input type="submit" value="Get links!">
			</form>
		<?php } ?>
		</div>















