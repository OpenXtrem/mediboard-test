CodeSniffer = {
  getFile: function (element) {
    var header = element.up('.tree-header');
    var path = header.id.match(/mediboard:(.*)-header/)[1];
    return path.replace(/:/g, '/');
  },
  
  
  auto: false,
  
  setAuto: function(input) {
    this.auto = input.checked;
  },
  
  index: 0,
  files: null,
  stats: null,

  run: function(button) {
	$('sniff-file').update();
    $('sniff-run').down('button.change').enable();
    $('sniff-run').down('input.auto').checked = this.auto = false;
    
	var run = $('sniff-run');
	var tbody = run.down('table tbody.files');
	tbody.update();
    modal(run);
    CodeSniffer.parse.bind(CodeSniffer).defer(button);
  },
  
  parse: function(button) {
    this.index = 0;
	var content = button.up('.tree-header').next();
	var sniffed = content.select('.sniffed');
	this.files = [];
	this.stats = {};
	
	sniffed.each(function(div) {
      var tag = $w(div.className)[1];
      CodeSniffer.files.push({
        path: CodeSniffer.getFile(div),
        tag: tag,
        status: null
      });
	  CodeSniffer.stats[tag] = CodeSniffer.stats[tag] ? CodeSniffer.stats[tag]+1 : 1;
	});
	
	var run = $('sniff-run');
	var tbody = run.down('table tbody.files');
	
	this.files.each(function(file) {
      tbody.insert(
        DOM.tr({ id: file.path.replace('/', ':') }, 
          DOM.td({}, file.path),
          DOM.td({}, 
            DOM.div({ className: 'sniffed ' + file.tag }),
            DOM.div({ className: 'status' })
          )
        )
      );
    });
	
	
  },
  
  start: function() {
	if (this.index == this.files.length) {
      return;
    }

	var file = this.files[this.index];
	
	var status = $(file.path.replace('/', ':')).down('.status');
	status.update(DOM.div({ className: 'loading' }, 'Running'));
	
	if (file.tag == 'uptodate') {
      status.update(DOM.div({ className: 'info' }, 'Skipped'));
	}
	else {
      var options = {
        onComplete: function() {
          status.update(DOM.div({ className: 'info' }, 'Done'));
          if (CodeSniffer.auto) {
            CodeSniffer.start();
          }
        }
      }
      
      new Url('developpement', 'sniff_file') .
      addParam('file', file.path) .
      requestUpdate('sniff-file', options);
	}
	
    this.index++;
	if (this.index == this.files.length) {
      $('sniff-run').down('button.change').disable();
    }

	if (file.tag == 'uptodate' && CodeSniffer.auto) {
      CodeSniffer.start();
	}
  },
  
  close: function() {
    this.auto = false;
	Control.Modal.close();
	window.location.reload();
  },

  show: function(button) {
    new Url('developpement', 'sniff_file') .
      addParam('file', this.getFile(button)) .
      requestModal(800, 400);
  }
}
