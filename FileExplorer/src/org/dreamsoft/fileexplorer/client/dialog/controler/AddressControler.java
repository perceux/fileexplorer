package org.dreamsoft.fileexplorer.client.dialog.controler;

import org.dreamsoft.fileexplorer.client.dialog.ui.AddressToolBar;

import com.extjs.gxt.ui.client.mvc.AppEvent;
import com.extjs.gxt.ui.client.mvc.Controller;


/**
 * Address bar controler
 * 
 * @author Eric Taix
 */
public class AddressControler extends Controller {

  private AddressToolBar panel;

  /**
   * Contructor
   */
  public AddressControler(AddressToolBar panelP) {
    panel = panelP;
    registerEventTypes(FilesEvents.DIRECTORY_CHANGED);
    panel.initHistory();
  }

  /**
   * Handles events fired by the dispatcher
   */
  public void handleEvent(AppEvent eventP) {
    if (eventP.getType().equals(FilesEvents.DIRECTORY_CHANGED)) {
      panel.changeDirectory((String)eventP.getData());
    }
  }
}
