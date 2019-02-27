var _smartsupp = _smartsupp || {};
_smartsupp.key = drupalSettings.uhsg_chat.key;
_smartsupp.offsetX = drupalSettings.uhsg_chat.offsetX;
window.smartsupp||(function(d) {
    var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
    s=d.getElementsByTagName('script')[0];c=d.createElement('script');
    c.type='text/javascript';c.charset='utf-8';c.async=true;
    c.src=drupalSettings.uhsg_chat.src+'?';s.parentNode.insertBefore(c,s);
})(document);

smartsupp('language', drupalSettings.uhsg_chat.currentLanguage);

// change agent name and note for all agents
smartsupp('on', 'agent.join', function(model, agent) {
  agent.note = drupalSettings.uhsg_chat.agentNote;
});

smartsupp('translate', {
  online: {
    title: drupalSettings.uhsg_chat.title
  }
}, drupalSettings.uhsg_chat.currentLanguage);

smartsupp('translate', {
  online: {
    infoTitle: drupalSettings.uhsg_chat.infoTitle
  }
}, drupalSettings.uhsg_chat.currentLanguage);

smartsupp('translate', {
  online: {
    infoDesc: drupalSettings.uhsg_chat.infoDesc
  }
}, drupalSettings.uhsg_chat.currentLanguage);