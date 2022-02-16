<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">

	<main class="dol-pb-8 dol--mt-24">
		<div class="dol-max-w-3xl dol-px-4 dol-mx-auto sm:dol-px-6 lg:dol-max-w-7xl lg:dol-px-8">
			<h1 class="dol-sr-only">Profile</h1>
			<!-- Main 3 column dol-grid -->
			<div class="dol-grid dol-items-start dol-grid-cols-1 dol-gap-4 lg:dol-grid-cols-3 lg:dol-gap-8">
				<!-- Left column -->
				<div class="dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">
					<!-- Welcome panel -->
					<section aria-labelledby="profile-overview-title">
						<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
							<h2 class="dol-sr-only" id="profile-overview-title">Profile Overview</h2>
							<div class="dol-p-6 dol-bg-white">
								<div class="sm:dol-flex sm:dol-items-center sm:dol-justify-between">
									<div class="sm:dol-flex sm:dol-space-x-5">
										<div class="dol-flex-shrink-0">
											<img class="dol-w-20 dol-h-20 dol-mx-auto dol-rounded-full" src="https://images.unsplash.com/photo-1550525811-e5869dd03032?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
										</div>
										<div class="dol-mt-4 dol-text-center sm:dol-mt-0 sm:dol-pt-1 sm:dol-text-left">
											<p class="dol-text-sm dol-font-medium dol-text-gray-600">Welcome back,</p>
											<p class="dol-text-xl dol-font-bold dol-text-gray-900 sm:dol-text-2xl">Chelsea Hagon</p>
											<p class="dol-text-sm dol-font-medium dol-text-gray-600">Human Resources Manager</p>
										</div>
									</div>
									<div class="dol-flex dol-justify-center dol-mt-5 sm:dol-mt-0">
										<a href="#" class="dol-flex dol-items-center dol-justify-center dol-px-4 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-700 dol-bg-white dol-border dol-border-gray-300 dol-rounded-md dol-shadow-sm hover:dol-bg-gray-50"> View profile </a>
									</div>
								</div>
							</div>
							<div class="dol-grid dol-grid-cols-1 dol-border-t dol-border-gray-200 dol-divide-y dol-divide-gray-200 dol-bg-gray-50 sm:dol-grid-cols-3 sm:dol-divide-y-0 sm:dol-divide-x">
								<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
									<span class="dol-text-gray-900"><?php echo dollie()->count_total_containers(); ?></span>
									<span class="dol-text-gray-600">Customer Sites</span>
								</div>

								<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
									<span class="dol-text-gray-900"><?php echo dollie()->count_total_blueprints(); ?></span>
									<span class="dol-text-gray-600">Site Blueprints</span>
								</div>

								<div class="dol-px-6 dol-py-5 dol-text-sm dol-font-medium dol-text-center">
									<span class="dol-text-gray-900"><?php echo dollie()->count_customer_staging_sites(); ?></span>
									<span class="dol-text-gray-600">Staging Sites</span>
								</div>
							</div>
						</div>
					</section>

					<?php

					$args = [
						'posts_per_page' => 3,
						'post_type'      => 'container',
						'post_status'    => 'publish',
					];

					$sites = new WP_Query($args);

					$data = [
						'sites'      => $sites,
						'query_data' => [
							'permalink'    => get_the_permalink(),
						],
					];

					dollie()->load_template('loop/sites-dashboard', $data, true);
					?>



					<!-- Actions panel -->
					<section aria-labelledby="quick-links-title">
						<div class="dol-overflow-hidden dol-bg-gray-200 dol-divide-y dol-divide-gray-200 dol-rounded-lg dol-shadow sm:dol-divide-y-0 sm:dol-grid sm:dol-grid-cols-2 sm:dol-gap-px">
							<h2 class="dol-sr-only" id="quick-links-title">Quick links</h2>

							<div class="dol-relative dol-p-6 dol-bg-white dol-rounded-tl-lg dol-rounded-tr-lg sm:dol-rounded-tr-none group focus-within:dol-ring-2 focus-within:dol-ring-inset focus-within:ring-cyan-500">
								<div>
									<span class="dol-inline-flex dol-p-3 dol-text-teal-700 dol-rounded-lg dol-bg-teal-50 dol-ring-4 dol-ring-white">
										<!-- Heroicon name: outline/clock -->
										<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
										</svg>
									</span>
								</div>
								<div class="dol-mt-8">
									<h3 class="dol-text-lg dol-font-medium">
										<a href="#" class="focus:dol-outline-none">
											<!-- Extend touch target to entire panel -->
											<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
											Request time off
										</a>
									</h3>
									<p class="dol-mt-2 dol-text-sm dol-text-gray-500">Doloribus dolores nostrum quia qui natus officia quod et dolorem. Sit repellendus qui ut at blanditiis et quo et molestiae.</p>
								</div>
								<span class="dol-absolute dol-text-gray-300 dol-pointer-events-none dol-top-6 dol-right-6 group-hover:dol-text-gray-400" aria-hidden="true">
									<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
										<path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z" />
									</svg>
								</span>
							</div>

							<div class="dol-relative dol-p-6 dol-bg-white sm:dol-rounded-tr-lg group focus-within:dol-ring-2 focus-within:dol-ring-inset focus-within:ring-cyan-500">
								<div>
									<span class="dol-inline-flex dol-p-3 dol-text-purple-700 dol-rounded-lg dol-bg-purple-50 dol-ring-4 dol-ring-white">
										<!-- Heroicon name: outline/badge-check -->
										<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
										</svg>
									</span>
								</div>
								<div class="dol-mt-8">
									<h3 class="dol-text-lg dol-font-medium">
										<a href="#" class="focus:dol-outline-none">
											<!-- Extend touch target to entire panel -->
											<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
											Benefits
										</a>
									</h3>
									<p class="dol-mt-2 dol-text-sm dol-text-gray-500">Doloribus dolores nostrum quia qui natus officia quod et dolorem. Sit repellendus qui ut at blanditiis et quo et molestiae.</p>
								</div>
								<span class="dol-absolute dol-text-gray-300 dol-pointer-events-none dol-top-6 dol-right-6 group-hover:dol-text-gray-400" aria-hidden="true">
									<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
										<path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z" />
									</svg>
								</span>
							</div>

							<div class="dol-relative dol-p-6 dol-bg-white group focus-within:dol-ring-2 focus-within:dol-ring-inset focus-within:ring-cyan-500">
								<div>
									<span class="dol-inline-flex dol-p-3 dol-rounded-lg bg-sky-50 text-sky-700 dol-ring-4 dol-ring-white">
										<!-- Heroicon name: outline/users -->
										<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
										</svg>
									</span>
								</div>
								<div class="dol-mt-8">
									<h3 class="dol-text-lg dol-font-medium">
										<a href="#" class="focus:dol-outline-none">
											<!-- Extend touch target to entire panel -->
											<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
											Schedule a one-on-one
										</a>
									</h3>
									<p class="dol-mt-2 dol-text-sm dol-text-gray-500">Doloribus dolores nostrum quia qui natus officia quod et dolorem. Sit repellendus qui ut at blanditiis et quo et molestiae.</p>
								</div>
								<span class="dol-absolute dol-text-gray-300 dol-pointer-events-none dol-top-6 dol-right-6 group-hover:dol-text-gray-400" aria-hidden="true">
									<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
										<path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z" />
									</svg>
								</span>
							</div>

							<div class="dol-relative dol-p-6 dol-bg-white group focus-within:dol-ring-2 focus-within:dol-ring-inset focus-within:ring-cyan-500">
								<div>
									<span class="dol-inline-flex dol-p-3 dol-text-yellow-700 dol-rounded-lg dol-bg-yellow-50 dol-ring-4 dol-ring-white">
										<!-- Heroicon name: outline/cash -->
										<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
										</svg>
									</span>
								</div>
								<div class="dol-mt-8">
									<h3 class="dol-text-lg dol-font-medium">
										<a href="#" class="focus:dol-outline-none">
											<!-- Extend touch target to entire panel -->
											<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
											Payroll
										</a>
									</h3>
									<p class="dol-mt-2 dol-text-sm dol-text-gray-500">Doloribus dolores nostrum quia qui natus officia quod et dolorem. Sit repellendus qui ut at blanditiis et quo et molestiae.</p>
								</div>
								<span class="dol-absolute dol-text-gray-300 dol-pointer-events-none dol-top-6 dol-right-6 group-hover:dol-text-gray-400" aria-hidden="true">
									<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
										<path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z" />
									</svg>
								</span>
							</div>

							<div class="dol-relative dol-p-6 dol-bg-white sm:dol-rounded-bl-lg group focus-within:dol-ring-2 focus-within:dol-ring-inset focus-within:ring-cyan-500">
								<div>
									<span class="dol-inline-flex dol-p-3 dol-rounded-lg bg-rose-50 text-rose-700 dol-ring-4 dol-ring-white">
										<!-- Heroicon name: outline/receipt-refund -->
										<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
										</svg>
									</span>
								</div>
								<div class="dol-mt-8">
									<h3 class="dol-text-lg dol-font-medium">
										<a href="#" class="focus:dol-outline-none">
											<!-- Extend touch target to entire panel -->
											<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
											Submit an expense
										</a>
									</h3>
									<p class="dol-mt-2 dol-text-sm dol-text-gray-500">Doloribus dolores nostrum quia qui natus officia quod et dolorem. Sit repellendus qui ut at blanditiis et quo et molestiae.</p>
								</div>
								<span class="dol-absolute dol-text-gray-300 dol-pointer-events-none dol-top-6 dol-right-6 group-hover:dol-text-gray-400" aria-hidden="true">
									<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
										<path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z" />
									</svg>
								</span>
							</div>

							<div class="dol-relative dol-p-6 dol-bg-white dol-rounded-bl-lg dol-rounded-br-lg sm:dol-rounded-bl-none group focus-within:dol-ring-2 focus-within:dol-ring-inset focus-within:ring-cyan-500">
								<div>
									<span class="dol-inline-flex dol-p-3 dol-text-indigo-700 dol-rounded-lg dol-bg-indigo-50 dol-ring-4 dol-ring-white">
										<!-- Heroicon name: outline/academic-cap -->
										<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
											<path d="M12 14l9-5-9-5-9 5 9 5z" />
											<path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
										</svg>
									</span>
								</div>
								<div class="dol-mt-8">
									<h3 class="dol-text-lg dol-font-medium">
										<a href="#" class="focus:dol-outline-none">
											<!-- Extend touch target to entire panel -->
											<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
											Training
										</a>
									</h3>
									<p class="dol-mt-2 dol-text-sm dol-text-gray-500">Doloribus dolores nostrum quia qui natus officia quod et dolorem. Sit repellendus qui ut at blanditiis et quo et molestiae.</p>
								</div>
								<span class="dol-absolute dol-text-gray-300 dol-pointer-events-none dol-top-6 dol-right-6 group-hover:dol-text-gray-400" aria-hidden="true">
									<svg class="dol-w-6 dol-h-6" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
										<path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z" />
									</svg>
								</span>
							</div>
						</div>
					</section>
				</div>

				<!-- Right column -->
				<div class="dol-grid dol-grid-cols-1 dol-gap-4">
					<!-- Announcements -->
					<section aria-labelledby="announcements-title">
						<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
							<div class="dol-p-6">
								<h2 class="dol-text-base dol-font-medium dol-text-gray-900" id="announcements-title">Announcements</h2>
								<div class="dol-flow-root dol-mt-6">

									<ul role="list" class="dol--my-5 dol-divide-y dol-divide-gray-200">
										<?php
										$cbox_plugins = CBox_Plugins::get_plugins();
										foreach (CBox_Admin_Plugins::get_settings() as $plugin => $settings_url) {
											echo '<li class="dol-py-5><a title="' . __("Click here to view this plugin's settings page", 'commons-in-a-box') . '" href="' . $settings_url . '">' . $plugin . '</a> - ' . $cbox_plugins[$plugin]['cbox_description'];

											if (!empty($cbox_plugins[$plugin]['documentation_url']))
												echo ' [<a title="' . __("Click here for plugin documentation at commonsinabox.org", 'commons-in-a-box') . '" href="' . esc_url($cbox_plugins[$plugin]['documentation_url']) . '" target="_blank">' . __('Info...', 'commons-in-a-box') . '</a>]';

											echo '</li>';
										}
										?>
									</ul>

									<div class="login postbox">
										<div class="message" style="text-align:center;">
											<strong><?php printf(__('<a href="%s">Manage all your CBOX plugins here</a>', 'commons-in-a-box'), esc_url(self_admin_url('admin.php?page=cbox-plugins'))); ?></strong>
										</div>
									</div>

									<ul role="list" class="dol--my-5 dol-divide-y dol-divide-gray-200">
										<li class="dol-py-5">
											<div class="dol-relative focus-within:dol-ring-2 focus-within:ring-cyan-500">
												<h3 class="dol-text-sm dol-font-semibold dol-text-gray-800">
													<a href="#" class="hover:dol-underline focus:dol-outline-none">
														<!-- Extend touch target to entire panel -->
														<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
														Office closed on July 2nd
													</a>
												</h3>
												<p class="dol-mt-1 dol-text-sm dol-text-gray-600 line-clamp-2">Cum qui rem deleniti. Suscipit in dolor veritatis sequi aut. Vero ut earum quis deleniti. Ut a sunt eum cum ut repudiandae possimus. Nihil ex tempora neque cum consectetur dolores.</p>
											</div>
										</li>

										<li class="dol-py-5">
											<div class="dol-relative focus-within:dol-ring-2 focus-within:ring-cyan-500">
												<h3 class="dol-text-sm dol-font-semibold dol-text-gray-800">
													<a href="#" class="hover:dol-underline focus:dol-outline-none">
														<!-- Extend touch target to entire panel -->
														<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
														New password policy
													</a>
												</h3>
												<p class="dol-mt-1 dol-text-sm dol-text-gray-600 line-clamp-2">Alias inventore ut autem optio voluptas et repellendus. Facere totam quaerat quam quo laudantium cumque eaque excepturi vel. Accusamus maxime ipsam reprehenderit rerum id repellendus rerum. Culpa cum vel natus. Est sit autem mollitia.</p>
											</div>
										</li>

										<li class="dol-py-5">
											<div class="dol-relative focus-within:dol-ring-2 focus-within:ring-cyan-500">
												<h3 class="dol-text-sm dol-font-semibold dol-text-gray-800">
													<a href="#" class="hover:dol-underline focus:dol-outline-none">
														<!-- Extend touch target to entire panel -->
														<span class="dol-absolute dol-inset-0" aria-hidden="true"></span>
														Office closed on July 2nd
													</a>
												</h3>
												<p class="dol-mt-1 dol-text-sm dol-text-gray-600 line-clamp-2">Tenetur libero voluptatem rerum occaecati qui est molestiae exercitationem. Voluptate quisquam iure assumenda consequatur ex et recusandae. Alias consectetur voluptatibus. Accusamus a ab dicta et. Consequatur quis dignissimos voluptatem nisi.</p>
											</div>
										</li>
									</ul>
								</div>
								<div class="dol-mt-6">
									<a href="#" class="dol-flex dol-items-center dol-justify-center dol-w-full dol-px-4 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-700 dol-bg-white dol-border dol-border-gray-300 dol-rounded-md dol-shadow-sm hover:dol-bg-gray-50"> View all </a>
								</div>
							</div>
						</div>
					</section>

					<!-- Recent Hires -->
					<section aria-labelledby="recent-hires-title">
						<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow">
							<div class="dol-p-6">
								<h2 class="dol-text-base dol-font-medium dol-text-gray-900" id="recent-hires-title">Recent Hires</h2>
								<div class="dol-flow-root dol-mt-6">
									<ul role="list" class="dol--my-5 dol-divide-y dol-divide-gray-200">
										<li class="dol-py-4">
											<div class="dol-flex dol-items-center dol-space-x-4">
												<div class="dol-flex-shrink-0">
													<img class="dol-w-8 dol-h-8 dol-rounded-full" src="https://images.unsplash.com/photo-1519345182560-3f2917c472ef?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
												</div>
												<div class="dol-flex-1 dol-min-w-0">
													<p class="dol-text-sm dol-font-medium dol-text-gray-900 dol-truncate">Leonard Krasner</p>
													<p class="dol-text-sm dol-text-gray-500 dol-truncate">@leonardkrasner</p>
												</div>
												<div>
													<a href="#" class="dol-inline-flex dol-items-center dol-shadow-sm dol-px-2.5 dol-py-0.5 dol-border dol-border-gray-300 dol-text-sm dol-leading-5 dol-font-medium dol-rounded-full dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> View </a>
												</div>
											</div>
										</li>

										<li class="dol-py-4">
											<div class="dol-flex dol-items-center dol-space-x-4">
												<div class="dol-flex-shrink-0">
													<img class="dol-w-8 dol-h-8 dol-rounded-full" src="https://images.unsplash.com/photo-1463453091185-61582044d556?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
												</div>
												<div class="dol-flex-1 dol-min-w-0">
													<p class="dol-text-sm dol-font-medium dol-text-gray-900 dol-truncate">Floyd Miles</p>
													<p class="dol-text-sm dol-text-gray-500 dol-truncate">@floydmiles</p>
												</div>
												<div>
													<a href="#" class="dol-inline-flex dol-items-center dol-shadow-sm dol-px-2.5 dol-py-0.5 dol-border dol-border-gray-300 dol-text-sm dol-leading-5 dol-font-medium dol-rounded-full dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> View </a>
												</div>
											</div>
										</li>

										<li class="dol-py-4">
											<div class="dol-flex dol-items-center dol-space-x-4">
												<div class="dol-flex-shrink-0">
													<img class="dol-w-8 dol-h-8 dol-rounded-full" src="https://images.unsplash.com/photo-1502685104226-ee32379fefbe?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
												</div>
												<div class="dol-flex-1 dol-min-w-0">
													<p class="dol-text-sm dol-font-medium dol-text-gray-900 dol-truncate">Emily Selman</p>
													<p class="dol-text-sm dol-text-gray-500 dol-truncate">@emilyselman</p>
												</div>
												<div>
													<a href="#" class="dol-inline-flex dol-items-center dol-shadow-sm dol-px-2.5 dol-py-0.5 dol-border dol-border-gray-300 dol-text-sm dol-leading-5 dol-font-medium dol-rounded-full dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> View </a>
												</div>
											</div>
										</li>

										<li class="dol-py-4">
											<div class="dol-flex dol-items-center dol-space-x-4">
												<div class="dol-flex-shrink-0">
													<img class="dol-w-8 dol-h-8 dol-rounded-full" src="https://images.unsplash.com/photo-1500917293891-ef795e70e1f6?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
												</div>
												<div class="dol-flex-1 dol-min-w-0">
													<p class="dol-text-sm dol-font-medium dol-text-gray-900 dol-truncate">Kristin Watson</p>
													<p class="dol-text-sm dol-text-gray-500 dol-truncate">@kristinwatson</p>
												</div>
												<div>
													<a href="#" class="dol-inline-flex dol-items-center dol-shadow-sm dol-px-2.5 dol-py-0.5 dol-border dol-border-gray-300 dol-text-sm dol-leading-5 dol-font-medium dol-rounded-full dol-text-gray-700 dol-bg-white hover:dol-bg-gray-50"> View </a>
												</div>
											</div>
										</li>
									</ul>
								</div>
								<div class="dol-mt-6">
									<a href="#" class="dol-flex dol-items-center dol-justify-center dol-w-full dol-px-4 dol-py-2 dol-text-sm dol-font-medium dol-text-gray-700 dol-bg-white dol-border dol-border-gray-300 dol-rounded-md dol-shadow-sm hover:dol-bg-gray-50"> View all </a>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
	</main>
	<footer>
		<div class="dol-max-w-3xl dol-px-4 dol-mx-auto sm:dol-px-6 lg:dol-px-8 lg:dol-max-w-7xl">
			<div class="dol-py-8 dol-text-sm dol-text-center dol-text-gray-500 dol-border-t dol-border-gray-200 sm:dol-text-left"><span class="dol-block sm:dol-inline">&copy; Dollie</span> <span class="dol-block sm:dol-inline">All rights reserved.</span></div>
		</div>
	</footer>

</div>
