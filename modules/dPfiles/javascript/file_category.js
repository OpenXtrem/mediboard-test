/**
 * $Id$
 *
 * @category Files
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */

FilesCategory = {
  modal_cat : null,

  loadList : function() {
    var url = new Url("files", "ajax_list_categories");
    url.requestUpdate('list_file_category');
  },

  openInfoReadFilesGuid : function(object_guid) {
    var url = new Url('files', "ajax_modal_object_files_category");
    url.addParam('object_guid', object_guid);
    url.requestModal("700", "500");
    url.modalObject.observe('afterClose', FilesCategory.iconInfoReadFilesGuid.curry(object_guid));
    FilesCategory.modal_cat = url;
  },

  reloadModal : function() {
    if (FilesCategory.modal_cat) {
      FilesCategory.modal_cat.refreshModal();
    }
  },

  iconInfoReadFilesGuid : function(object_guid) {
    var url = new Url('files', "ajax_check_object_files_category");
    url.addParam('object_guid', object_guid);
    url.requestUpdate(object_guid+"_check_category");
  },

  edit : function(category_id) {
    var url = new Url("files", "ajax_edit_category");
    url.addParam("category_id", category_id);
    url.requestUpdate('edit_file_category');
  },

  callback : function(id) {
    FilesCategory.loadList();
    FilesCategory.edit(id);
  },

  checkMergeSelected : function(oinput) {
    var selected = $$("#list_file_categories input:checked");

    if (selected.length > 2) {
      //$(selected)[0].checked = false;
      $(oinput).checked = false;    // unckeck the last
    }
  },

  mergeSelected : function() {
    var selected = $$("#list_file_categories input:checked");
    if (selected.length < 2) {
      return;
    }

    var objects_id = [];
    selected.each(function(element) {
      objects_id.push($(element).get("id"));
    });

    var elements = objects_id.join('-');

    var url = new Url("system", "object_merger");
    url.addParam('objects_class', 'CFilesCategory');
    url.addParam('objects_id', elements);
    url.addParam('mode', 'fast');
    url.popup(800, 600, "merge_patients");
  }
};

onMergeComplete = function() {
  FilesCategory.loadList();
};