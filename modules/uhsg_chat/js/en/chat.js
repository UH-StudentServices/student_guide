var _smartsupp = _smartsupp || {};
_smartsupp.key = 'f9f5836c53b1a73c11565290d418e91b518e9163';
_smartsupp.offsetX = 100;
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);

smartsupp('language','en');

// change agent name and note for all agents
smartsupp('on', 'agent.join', function(model, agent) {
  agent.note = 'Student advisor';
});

smartsupp('translate', {
  online: {
    title: 'Student Services'
  }
}, 'en');

smartsupp('translate', {
  online: {
    infoTitle: 'University of Helsinki',
  }
}, 'en');

smartsupp('translate', {
  online: {
    infoDesc: 'How can I help you?',
  }
}, 'en');
