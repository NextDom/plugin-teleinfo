<?php
if (!isConnect('admin')) {
    throw new Exception('401 Unauthorized');
}
?>

<div class="container-modal">
    <div class="stepwizard col-md-offset-3">
        <div class="stepwizard-row setup-panel">
            <div class="stepwizard-step">
                <a href="#step-1" type="button" class="btn btn-primary stepwizard-btn-circle">1</a>
                <p>{{Étape 1}}</p>
            </div>
            <div class="stepwizard-step">
                <a href="#step-2" type="button" class="btn btn-default stepwizard-btn-circle" disabled="disabled">2</a>
                <p>{{Étape 2}}</p>
            </div>
            <div class="stepwizard-step">
                <a href="#step-3" type="button" class="btn btn-default stepwizard-btn-circle" disabled="disabled">3</a>
                <p>{{Étape 3}}</p>
            </div>
            <div class="stepwizard-step">
                <a href="#step-4" type="button" class="btn btn-default stepwizard-btn-circle" disabled="disabled">4</a>
                <p>{{Étape 4}}</p>
            </div>
            <div class="stepwizard-step">
                <a href="#step-5" type="button" class="btn btn-default stepwizard-btn-circle" disabled="disabled">5</a>
                <p>{{Étape 5}}</p>
            </div>
            <div class="stepwizard-step">
                <a href="#step-6" type="button" class="btn btn-default stepwizard-btn-circle" disabled="disabled">6</a>
                <p>{{Etape 6}}</p>
            </div>
        </div>
    </div>

    <form role="form" action="" method="post">
        <div class="row setup-content" id="step-1">
            <div class="col-xs-8 col-md-offset-3">
                <div class="col-md-10">
                    <center><h3>{{Vérifier la configuration du plugin}}</h3></center></br>
                    <div class="form-group">
                        <div class="row">
                        	<center><span style="font-size : 1em;"><a class="btn btn-sm btn-success" btnid="1"><i class="fas fa-play-circle"></i> {{Démarrer}}</a></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="result1" style=""></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="advise1" style=""></span></center>
                        </div>
                        <br>
                    </div>
                    <div style="background-color: #ccc;height:2px;margin-bottom:5px"></div>
                    <button id="toStep2" class="btn btn-primary nextBtn btn-lg pull-right" type="button" ><i class="fas fa-fast-forward"></i> {{Suivant}}</button>
                </div>
            </div>
        </div>
        <div class="row setup-content" id="step-2">
            <div class="col-xs-8 col-md-offset-3">
                <div class="col-md-10">
                    <center><h3>{{Modem connecté}}</h3></center></br>
                    <div class="form-group">
                        <div class="row">
                        	<center><span style="font-size : 1em;"><a class="btn btn-sm btn-success" btnid="2"><i class="fas fa-play-circle"></i> {{Démarrer}}</a></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="result2" style=""></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="advise2" style="display:none">{{Vérifier la connexion du modem à Jeedom}}</span></center>
                        </div>
                        <br>
                    </div>
                    <div style="background-color: #ccc;height:2px;margin-bottom:5px"></div>
                    <button id="toStep3" class="btn btn-primary nextBtn btn-lg pull-right" type="button" ><i class="fas fa-fast-forward"></i> {{Suivant}}</button>
                </div>
            </div>
        </div>
        <div class="row setup-content" id="step-3">
            <div class="col-xs-8 col-md-offset-3">
                <div class="col-md-10">
                    <center><h3>{{Accès au Modem}}</h3></center></br>
                    <div class="form-group">
                        <div class="row">
                        	<center><span style="font-size : 1em;"><a class="btn btn-sm btn-success" btnid="3"><i class="fas fa-play-circle"></i> {{Démarrer}}</a></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="result3" style=""></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="advise3" style="display:none">{{Vérifier les droits sur le port du Modem}}</span></center>
                        </div>
                        <br>
                    </div>
                    <div style="background-color: #ccc;height:2px;margin-bottom:5px"></div>
                    <button id="toStep4" class="btn btn-primary nextBtn btn-lg pull-right" type="button" ><i class="fas fa-fast-forward"></i> {{Suivant}}</button>
                </div>
            </div>
        </div>
        <div class="row setup-content" id="step-4">
            <div class="col-xs-8 col-md-offset-3">
                <div class="col-md-10">
                    <center><h3>{{Lecture des données}}</h3></center></br>
                    <div class="form-group">
                        <div class="row">
                        	<center><span style="font-size : 1em;"><a class="btn btn-sm btn-success" btnid="4"><i class="fas fa-play-circle"></i> {{Démarrer}}</a></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="result4" style=""></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="advise4" style="display:none">{{Vérifier que les cables soient bien connectés et que la téléinformation est bien activée par EDF}}</span></center>
                        </div>
                        <br>
                    </div>
                    <div style="background-color: #ccc;height:2px;margin-bottom:5px"></div>
                    <button id="toStep5" class="btn btn-primary nextBtn btn-lg pull-right" type="button" ><i class="fas fa-fast-forward"></i> {{Suivant}}</button>
                </div>
            </div>
        </div>
        <div class="row setup-content" id="step-5">
            <div class="col-xs-8 col-md-offset-3">
                <div class="col-md-10">
                    <center><h3>{{Intégritée des données}}</h3></center></br>
                    <div class="form-group">
                        <div class="row">
                        	<center><span style="font-size : 1em;"><a class="btn btn-sm btn-success" btnid="5"><i class="fas fa-play-circle"></i> {{Démarrer}}</a></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="result5" style=""></span></center>
                        </div>
                        <div class="row" style="margin-top:10px">
                        	<center><span class="advise5" style="display:none">{{"}}</span></center>
                        </div>
                        <br>
                    </div>
                    <div style="background-color: #ccc;height:2px;margin-bottom:5px"></div>
                    <button id="toStep6" class="btn btn-primary nextBtn btn-lg pull-right" type="button" ><i class="fas fa-fast-forward"></i> {{Suivant}}</button>
                </div>
            </div>
        </div>
        <div class="row setup-content" id="step-6">
            <div class="col-xs-8 col-md-offset-3">
                <div class="col-md-10">
                    <center><h3>{{Package de logs}}</h3></center></br>
                    <div class="form-group">
                        <div class="row">
                        	<center><span style="font-size : 1em;"><a class="btn btn-sm btn-success" btnid="6"><i class="fas fa-play-circle"></i> {{Démarrer}}</a></span></center>
                        </div>

                        <div class="row" style="margin-top:10px">
                        	<center><span class="result6" style=""></span></center>
                        </div>
                        <label class="control-label">Rappel des vérifications :</label>
                        <center><table class="">
                            <thead>
                                <tr>
                                    <th style="width:80px;">{{}}</th>
                                    <th>{{}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-weight: 700;">Etape 1 :</td>
                                    <td><span class="result1" style=""></span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700;">Etape 2 :</td>
                                    <td><span class="result2" style=""></span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700;">Etape 3 :</td>
                                    <td><span class="result3" style=""></span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700;">Etape 4 :</td>
                                    <td><span class="result4" style=""></span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700;">Etape 5 :</td>
                                    <td><span class="result5" style=""></span></td>
                                </tr>
                            </tbody>
                        </table></center>

                        <br>
                    </div>
                    <div style="background-color: #ccc;height:2px;margin-bottom:5px"></div>
                    <button id="closeConfigureModal" class="btn btn-primary nextBtn btn-lg pull-right ui-icon-closethick" type="button" >{{Terminer}}</button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include_file('desktop', 'diagnostic', 'js', 'teleinfo');?>
<?php include_file('desktop', 'diagnostic', 'css', 'teleinfo');?>
