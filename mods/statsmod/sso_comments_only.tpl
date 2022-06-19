							<div id="kl-detail-ssocomment-list">
								{section name=i loop=$comments}
								<div class="ssocomment-posted">
									<div class="ssocomment-avatar">
										<img src={$comments[i].avatar}>
									</div>
									<div class="ssocomment-text">
										<a href="{$kb_host}/?a=search&amp;searchtype=pilot&amp;searchphrase={$comments[i].encoded_name}">{$comments[i].name}</a>:
										{if $comments[i].time}
										<span class="ssocomment-time">{$comments[i].time}</span>
										{/if}
										<p>{$comments[i].comment}</p>
										{if $page->isAdmin()}
										<a href='{$kb_host}/?a=admin_comments_delete&amp;c_id={$comments[i].id}' onclick="openWindow('?a=admin_comments_delete&amp;c_id={$comments[i].id}', null, 480, 350, '' ); return false;">Delete Comment</a><br/>
										<span class="ssocomment-IP">Posters IP:{$comments[i].ip}</span><br/>
										{/if}
									</div>
								</div>
								{/section}
							</div>
