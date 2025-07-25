<div class="col-xxl-3 col-lg-4 col-md-5">
    <div class="myy-card shadow">
        <div class="myy-card__body text-center">
            <h6>{{ auth()->user()->name }}</h6>
            <p>{{ auth()->user()->email }}</p>
            <div class="myy-card__body-content mt-4">
                <div class="myy-card__body-content mt-4">
                    <div class="custom-nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                        aria-orientation="vertical">

                        <button class="nav-link active mb-3" id="v-pills-general-settings-tab" data-bs-toggle="pill"
                            data-bs-target="#v-pills-general-settings" type="button" role="tab"
                            aria-controls="v-pills-general-settings" aria-selected="true">

                            <i class="lni lni-cog"></i>
                            <span>{{ __('default.general_setting') }}</span>
                        </button>


                        <button class="nav-link mb-3" id="v-pillscol-md-10 col-lg-8 v-pills-theme-setting-tab"
                            data-bs-toggle="pill" data-bs-target="#v-pills-theme-setting" type="button" role="tab"
                            aria-controls="v-pills-theme-setting" aria-selected="false">
                            <span class="myy-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                                  color="#8b8181" fill="none">
                                  <path
                                    d="M11.0029 2H10.0062C6.72443 2 5.08355 2 3.92039 2.81382C3.49006 3.1149 3.11577 3.48891 2.81445 3.91891C2 5.08116 2 6.72077 2 10C2 13.2792 2 14.9188 2.81445 16.0811C3.11577 16.5111 3.49006 16.8851 3.92039 17.1862C5.08355 18 6.72443 18 10.0062 18H14.0093C17.2911 18 18.932 18 20.0951 17.1862C20.5254 16.8851 20.8997 16.5111 21.2011 16.0811C21.8156 15.2042 21.9663 14.0941 22 13"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                  <path
                                    d="M18 9.71428V11M18 9.71428C16.8432 9.71428 15.8241 9.14608 15.2263 8.28331M18 9.71428C19.1568 9.71428 20.1759 9.14608 20.7737 8.28331M18 3.28571C19.1569 3.28571 20.1761 3.854 20.7738 4.71688M18 3.28571C16.8431 3.28571 15.8239 3.854 15.2262 4.71688M18 3.28571V2M22 3.92857L20.7738 4.71688M14.0004 9.07143L15.2263 8.28331M14 3.92857L15.2262 4.71688M21.9996 9.07143L20.7737 8.28331M20.7738 4.71688C21.1273 5.22711 21.3333 5.84035 21.3333 6.5C21.3333 7.15973 21.1272 7.77304 20.7737 8.28331M15.2262 4.71688C14.8727 5.22711 14.6667 5.84035 14.6667 6.5C14.6667 7.15973 14.8728 7.77304 15.2263 8.28331"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                  <path d="M11 15H13" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                  <path d="M12 18V22" stroke="currentColor" stroke-width="1.5" />
                                  <path d="M8 22H16" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" />
                                </svg>
                            </span>
                            <span>Theme Setting</span>
                        </button>

                        <button class="nav-link mb-3" id="v-pillscol-md-10 col-lg-8 v-pills-company-info-tab"
                            data-bs-toggle="pill" data-bs-target="#v-pills-company-info" type="button" role="tab"
                            aria-controls="v-pills-company-info" aria-selected="false"><i
                                class="lni lni-home"></i>
                            <span>{{ __('default.company_info') }}</span>
                        </button>

                        <button class="nav-link mb-3" id="v-pillscol-md-10 col-lg-8 v-pills-policy-tab"
                            data-bs-toggle="pill" data-bs-target="#v-pills-policy" type="button" role="tab"
                            aria-controls="v-pills-policy" aria-selected="false"><i class="lni lni-protection"></i>
                            <span>{{ __('default.policy_setting') }}</span>
                        </button>

                        <button class="nav-link mb-3" id="v-pillscol-md-10 col-lg-8 v-pills-email_setting-tab"
                            data-bs-toggle="pill" data-bs-target="#v-pills-email_setting" type="button" role="tab"
                            aria-controls="v-pills-email_setting" aria-selected="false"><i class="lni lni-envelope"></i>
                            <span>{{ __('default.email_setting') }}</span>
                        </button>

                        @if (env('APP_DEBUG'))
                            <button class="nav-link mb-3" id="v-pillscol-md-10 col-lg-8 v-pills-developmemnt_tools-tab"
                                data-bs-toggle="pill" data-bs-target="#v-pills-developmemnt_tools" type="button"
                                role="tab" aria-controls="v-pills-developmemnt_tools" aria-selected="false"><i
                                    class="lni lni-code"></i>
                                <span>{{ __('default.dev_tools') }}</span>
                            </button>
                        @endif




                        <div id="sidebar-btn"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
