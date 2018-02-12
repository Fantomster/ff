<?php

namespace common\widgets\setinfo;

use yii\base\Widget;

/**
 * Set organization address etc at popup form
 *
 * @author elbabuino
 */
class SetInfoWidget extends Widget {

    public $action = '';
    public $id = 'data-modal-wizard';
    public $organization;
    public $profile;
    public $events;
    public $selector;

    public function init() {
        parent::init();

        $this->view->registerJs('
            function stopRKey(evt) { 
                var evt = (evt) ? evt : ((event) ? event : null); 
                var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
                if ((evt.keyCode == 13) && (node.type=="text")) {return false;} 
            } 

            document.onkeypress = stopRKey; 

            $(document).on("click", ".next", function(e) {
                e.preventDefault();
                $(".data-modal .modal-content").slick("slickNext");
            });

            $(document).on("submit", "#complete-form", function() {
                var form = $(this);
                $.post(
                    form.attr("action"),
                    form.serialize()
                ).done(function(result) {
                    if (result.length == 0) {
                        document.location.reload();
                    }
                });
                return false;
            });

            $(document).on("afterValidate", "#complete-form", function(event, messages, errorAttributes) {
                for (var input in messages) {
                    if (messages[input] != "") {
                        $("#" + input).tooltip({title: messages[input], placement: "auto right", container: "body"});
                        $("#" + input).tooltip();
                        $("#" + input).tooltip("show");
                        return;
                    }
                }
            });

            $("#' . $this->id . '").on("shown.bs.modal",function(){
                $(".data-modal .modal-content").slick({arrows:!1,dots:!1,swipe:!1,infinite:!1,adaptiveHeight:!0})
                initMap();
            });
            $("body").on("hidden.bs.modal", "#' . $this->id . '", function() {
                document.location.reload();
            })
            $(document).on("'.$this->events.'", "'.$this->selector.'", function(e) {
                if ($(this).attr("href") === "#") {
                    e.preventDefault();
                }
                $("#'.$this->id.'").modal({show:true});
            })
        ', \yii\web\View::POS_READY);
    }

    public function run() {
        $asset = SetInfoWidgetAsset::register($this->getView());
        \common\assets\GoogleMapsAsset::register($this->getView());
        $baseUrl = $asset->baseUrl;
        return $this->render('_set-info', [
                    'baseUrl' => $baseUrl,
                    'action' => $this->action,
                    'id' => $this->id,
                    'organization' => $this->organization,
                    'profile' => $this->profile,
        ]);
    }

}
