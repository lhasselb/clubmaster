        <!-- BEGIN: PAGE CONTAINER -->
        <div class="c-layout-page">
            <!-- BEGIN: LAYOUT/BREADCRUMBS/BREADCRUMBS-1 -->
            <div class="c-layout-breadcrumbs-1 c-fonts-uppercase c-fonts-bold c-bordered c-bordered-both">
                <div class="container">
                    <div class="c-page-title c-pull-left">
                        <h2 class="c-font-uppercase c-font-sbold">$Title</h2>
                    </div>
                    <% include BreadCrumbs %>
                </div>
            </div>
            <!-- END: LAYOUT/BREADCRUMBS/BREADCRUMBS-1 -->
            <!-- BEGIN: PAGE CONTENT -->
            <div class="c-content-box c-size-lg c-overflow-hide c-bg-white" role="main">
                <div class="container">
                    <div class="alert alert-success" role="alert">Diese Information wurde gesendet an $FormData.Email</div>
                    <div class="c-shop-order-complete-1 c-content-bar-1 c-align-left c-bordered c-theme-border c-shadow">
                        <div class="c-content-title-1">
                            <h3 class="c-center c-font-uppercase c-font-bold">$Title</h3>
                            <div class="c-line-center c-theme-bg"></div>
                        </div>
                        <div class="c-theme-bg">
                            <p class="c-message c-center c-font-white c-font-20 c-font-sbold">
                                <i class="fa fa-check"></i>$Content</p>
                        </div>
                        <% with $FormData %>
                        <div class="c-customer-details row" data-auto-height="true">
                            <div class="col-md-6 col-sm-6 c-margin-t-20">
                                <div data-height="height">
                                    <h3 class=" c-margin-b-20 c-font-uppercase c-font-22 c-font-bold">Kontaktdaten</h3>
                                    <ul class="list-unstyled">
                                        <li>Name: $Salutation $FirstName $LastName</li>
                                        <li>Geburtstag: $Birthday</li>
                                        <li>Adressse: $Street $StreetNumber <br />$Nationality - $Zip $City</li>
                                        <li>E-Mail: $Email</li>
                                        <li>Mobil: $Mobil</li>
                                        <li>Telefon: $Phone</li>
                                        <li>Typ: $TypeID</li>
                                        <li>Mitgliedschaft startet am $Since</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 c-margin-t-20">
                                <div data-height="height">
                                    <h3 class=" c-margin-b-20 c-font-uppercase c-font-22 c-font-bold">Bankverbindung</h3>
                                    <ul class="list-unstyled">
                                        <li>$AccountHolderFirstName $AccountHolderLastName</li>
                                        <li>$AccountHolderStreet $AccountHolderStreetNumber <br />$AccountHolderZip $AccountHolderCity</li>
                                        <li>$Iban</li>
                                        <li>$Bic</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <% end_with %>
                    </div>
                </div>
            </div>
            <!-- END: PAGE CONTENT -->
        </div>
        <!-- END: PAGE CONTAINER -->
