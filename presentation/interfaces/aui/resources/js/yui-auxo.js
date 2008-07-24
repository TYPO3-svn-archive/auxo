// set 'auxo' namespace
YAHOO.namespace('auxo');

// register new widgets
// 
(function(){
 
  // Shortcuts to some YUI library used in this example:
  var Dom   = YAHOO.util.Dom,
      Event = YAHOO.util.Event,
      Panel = YAHOO.widget.Panel;
  
  YAHOO.auxo.loading = function(id) {               
    YAHOO.auxo.loading.superclass.constructor.call(this, 
        id || Dom.generateId() , 
        {
            width: "100px", 
            fixedcenter: true, 
            constraintoviewport: true, 
            underlay: "shadow", 
            close: false, 
            visible: false, 
            draggable: true
        }
    );

    this.setHeader("Loading ...");
    this.setBody('<img src="../aui/lib/resources/assets/loading.gif" />');
    Dom.setStyle(this.body, 'textAlign', 'center');
    this.render(document.body);
  };

  YAHOO.extend(YAHOO.auxo.loading, Panel);
})();

/ It is important that loaded objects register themselves so the YUI Loader, and the YUI library as a whole,
// knows what it has loaded.  
// If this declaration is missing, the Loader would wait indefinitely for it to finish loading and initializing
// (well, at least that is what it would assume it is doing)
// It is important that this line be the last one.

YAHOO.register('auxo.loading', YAHOO.auxo.loading, {version: "0.99", build: '11'});