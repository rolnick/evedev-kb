<script>
    xajax_getSSOComments({$sso_kill_id});
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer> </script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<link rel="stylesheet" href="{$kb_host}/mods/statsmod/style.css" type="text/css"/>
<div class="kl-detail-comments">
	<div class="block-header">Comments</div>
	<form id="postform" method="post" action="{$formURL} ">
<table>
    <tr>
        <td><input class="comment" name="comment" type=hidden  value="Fit z dupy"/></td>
    </tr>
    <tr>
    <td><br/><div class="g-recaptcha" data-sitekey="6LfCnq0UAAAAANqRM4dt8MXV14oWsKZGhxHpfA-4" data-callback="enableBtn"></div>
            <button  class="comment-button" name="submit" type="submit" style="border: 0; background: transparent" id="submit_details" value="" disabled /><img src="/img/button_fit-z-dupy.png" /></button></td>
    </tr>
</table>
</form>
<script> function enableBtn(){
    document.getElementById("submit_details").disabled = false;
    }
</script>
	<table class="kb-table">
		<tr>
			<td class="kl-detail-comments-outer" >
				<table class="kl-detail-comments-inner">
					<tr>
						<td>
							<div id="kl-detail-ssocomment-list">
								{section name=i loop=$comments}
								<div class="comment-posted"><img src={$comments[i].avatar}><a href="{$kb_host}/?a=search&amp;searchtype=pilot&amp;searchphrase={$comments[i].encoded_name}">{$comments[i].name}</a>:
						{if $comments[i].time}
									<span class="comment-time">{$comments[i].time}</span>
						{/if}
									<p>{$comments[i].comment}</p>
						{if $page->isAdmin()}
									<a href='{$kb_host}/?a=admin_comments_delete&amp;c_id={$comments[i].id}' onclick="openWindow('?a=admin_comments_delete&amp;c_id={$comments[i].id}', null, 480, 350, '' ); return false;">Delete Comment</a><br/>
									<span class="comment-IP">Posters IP:{$comments[i].ip}</span><br/>
						{/if}
								</div>
								{/section}
							</div>
						</td>
					</tr>
					<tr>
						<td>
                                                        {if $comment_allowed}
							<form id="postform" method="post" action="{$ssocommentformURL}">
								<table>
									<tr>
										<td>
											<textarea class="comment" name="sso_comment" cols="55" rows="5" style="width:97%" onkeyup="limitText(this.form.sso_comment,document.getElementById('countdown'),500);" onkeypress="limitText(this.form.sso_comment,document.getElementById('countdown'),500);"></textarea>
										</td>
									</tr>
									<tr>
										<td>
											<span title="countdown" id="countdown">500</span> Letters left<br/>
											<input class="comment-button" name="eve_sso" type="submit" value="Post as {$sso_pilot}" />
										</td>
									</tr>
								</table>
							</form>
                                                        {else}
                                                        <b>{$comment_disallowed_reason}</b>
                                                        {/if}
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

