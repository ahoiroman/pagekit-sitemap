<?php $view->script( 'settings', 'sitemap:app/bundle/settings.js', [ 'vue' ] ); ?>

<div id="settings" class="uk-form uk-form-horizontal" v-cloak>
	<div class="uk-grid pk-grid-large" data-uk-grid-margin>
		<div class="pk-width-sidebar">
			<div class="uk-panel">
				<ul class="uk-nav uk-nav-side pk-nav-large" data-uk-tab="{ connect: '#tab-content' }">
					<li><a><i class="pk-icon-large-settings uk-margin-right"></i> {{ 'General' | trans }}</a></li>
					<li><a><i class="uk-icon-puzzle-piece uk-margin-right"></i> {{ 'Exclusions' | trans }}</a></li>
					<li><a><i class="pk-icon-large-cone uk-margin-right"></i>
							{{ 'Info' | trans }}</a></li>
				</ul>
			</div>
		</div>
		<div class="pk-width-content">
			<ul id="tab-content" class="uk-switcher uk-margin">
				<li>
					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'General' | trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}
							</button>
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-country" class="uk-form-label">{{ 'Frequency' | trans }}</label>
						<div class="uk-form-controls">
							<select id="form-country" v-model="config.frequency">
								<option value="daily">{{ 'Daily' | trans }}</option>
								<option value="weekly">{{ 'Weekly' | trans }}</option>
								<option value="monthly">{{ 'Monthly' | trans }}</option>
								<option value="yearly">{{ 'Yearly' | trans }}</option>
							</select>
						</div>
					</div>
					<div class="uk-form-row">
						<label class="uk-form-label">{{ 'Filename' | trans }}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<p class="uk-form-controls-condensed">
								<input type="text" v-model="config.filename">
							</p>
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-verifyssl"
						       class="uk-form-label">{{ 'Verify SSL' | trans }}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<input id="form-verifyssl" type="checkbox"
							       v-model="config.verifyssl">
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-allowredirects"
						       class="uk-form-label">{{ 'Allow redirects' | trans }}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<input id="form-allowredirects" type="checkbox"
							       v-model="config.allowredirects">
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-debug"
						       class="uk-form-label">{{ 'Enable debug mode' | trans }}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<input id="form-debug" type="checkbox"
							       v-model="config.debug">
						</div>
					</div>
					<div class="uk-form-row">
						<button v-if="!progress" class="uk-button uk-button-secondary uk-button-large" @click.prevent="generate">
							<span>{{ 'Generate' | trans }}</span>
						</button>
						<button v-else class="uk-button uk-button-secondary uk-button-large" disabled>
							<span><i class="uk-icon-spinner uk-icon-spin"></i> {{ 'Crawling' | trans }}</span>
						</button>
					</div>
				</li>
				<li>
					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'Exclusions' | trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}
							</button>
						</div>
					</div>
					<form class="uk-form uk-form-stacked" v-validator="formExclusions" @submit.prevent="add | valid">
						<div class="uk-form-row">
							<div class="uk-grid" data-uk-margin>
								<div class="uk-width-large-1-2">
									<input class="uk-input-large"
									       type="text"
									       placeholder="{{ 'URL' | trans }}"
									       name="exclusion"
									       v-model="newExclusion"
									       v-validate:required>
									<p class="uk-form-help-block uk-text-danger" v-show="formExclusions.exclusion.invalid">
										{{ 'Invalid value.' | trans }}</p>
								</div>
								<div class="uk-width-large-1-2">
									<div class="uk-form-controls">
										<span class="uk-align-right">
											<button class="uk-button" @click.prevent="add | valid">
												{{ 'Add' | trans }}
											</button>
										</span>
									</div>
								</div>
							</div>
						</div>
					</form>
					<hr>
					<div class="uk-alert"
					     v-if="!config.excluded.length">{{ 'You can add your first exclusion using the input field above. Go ahead!' | trans }}
					</div>
					<ul class="uk-list uk-list-line" v-if="config.excluded.length">
						<li v-for="exclusion in config.excluded">
							<input class="uk-input-large"
							       type="text"
							       placeholder="{{ 'URL' | trans }}"
							       v-model="exclusion">
							<span class="uk-align-right">
								<button @click="remove(exclusion)" class="uk-button uk-button-danger">
									<i class="uk-icon-remove"></i>
								</button>
							</span>
						</li>
					</ul>
				</li>
				<li>
					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap"
					     data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'Info' | trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary"
							        @click.prevent="save">{{ 'Save' | trans }}
							</button>
						</div>
					</div>
					<div class="uk-form-row">
						<label class="uk-form-label">{{ 'Getting help' | trans }}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<div class="uk-panel uk-panel-box">
								<p>{{ 'You have problems using this extension? Join the Pagekit community forum.' | trans }}</p>
								<a class="uk-button uk-width-1-1 uk-button-large"
								   href="https://pagekit-forum.org"
								   target="_blank">Pagekit Forum</a>
							</div>
						</div>
					</div>
					<div class="uk-form-row">
						<label class="uk-form-label">{{ 'Donate' | trans }}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<div class="uk-panel uk-panel-box">
								<p>{{ 'Do you like my extensions? They are free. Of course I would like to get a donation, so if you want to, please open the donate link. You may find three possibilities to donate: PayPal, Patreon and Coinhive.' | trans }} </p>
								<a class="uk-button uk-button-large uk-width-1-1 uk-button-primary"
								   href="https://spqr.wtf/support-me"
								   target="_blank">Donate</a>
							</div>
						</div>
					</div>
				</li>

			</ul>
		</div>
	</div>
</div>