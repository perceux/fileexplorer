/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.ui;

import org.dreamsoft.fileexplorer.client.dialog.controler.FilesEvents;
import org.dreamsoft.fileexplorer.client.dialog.model.Favorite;

import com.extjs.gxt.ui.client.Style.Scroll;
import com.extjs.gxt.ui.client.event.ComponentEvent;
import com.extjs.gxt.ui.client.event.Events;
import com.extjs.gxt.ui.client.event.Listener;
import com.extjs.gxt.ui.client.widget.ContentPanel;
import com.extjs.gxt.ui.client.widget.DataList;
import com.extjs.gxt.ui.client.widget.DataListItem;
import com.extjs.gxt.ui.client.widget.layout.AccordionLayout;
import com.extjs.gxt.ui.client.widget.layout.FitLayout;


/**
 * The panel which contains directories and favorites directories or links
 * 
 * @author Eric Taix
 */
public class NavigationPanel extends ContentPanel {

  // The favorite list
  private DataList favList;
  // The listener
  private Listener<ComponentEvent> listener;

  /**
   * Constructor
   */
  public NavigationPanel() {
    setLayout(new AccordionLayout());
    setBorders(false);

    // The favorite panel
    ContentPanel favoritePane = new ContentPanel();
    favoritePane.setBorders(true);
    favoritePane.setBodyBorder(false);
    favoritePane.setLayout(new FitLayout());
    favoritePane.setHeading("Favorites");
    favoritePane.setScrollMode(Scroll.AUTO);
    add(favoritePane);

    favList = new DataList();
    favList.setBorders(false);
    favoritePane.add(favList);

    listener = new Listener<ComponentEvent>() {
      public void handleEvent(ComponentEvent ce) {
        DataList dataList = (DataList) ce.getComponent();
        DataListItem item = dataList.getSelectedItem();
        String dir = (String) item.getData("directory");
        FilesEvents.fireDirectoryChanged(dir);
      }
    };
    favList.addListener(Events.SelectionChange, listener);
    
    // The directory panel
    add(new DirectoryPanel("http://localhost:8080/php/file.php"));
    
    
    // The history panel
    ContentPanel histoPane = new ContentPanel();
    histoPane.setBorders(true);
    histoPane.setBodyBorder(false);
    histoPane.setLayout(new FitLayout());
    histoPane.setHeading("History");
    histoPane.setScrollMode(Scroll.AUTO);
    add(histoPane);
    
  }

  /**
   * Add a new favorite
   * 
   * @param favP
   */
  public void addFavorite(Favorite favP) {
    DataListItem item = new DataListItem();      
    item.setText(favP.getTitle());  
    item.setIconStyle(favP.getIconStyle());  
    item.setData("directory", favP.getDirectory());
    favList.add(item);
  }
}
