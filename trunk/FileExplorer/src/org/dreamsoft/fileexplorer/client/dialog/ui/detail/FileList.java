/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.ui.detail;

import org.dreamsoft.fileexplorer.client.dialog.controler.FilesEvents;
import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.Style.SelectionMode;
import com.extjs.gxt.ui.client.event.Events;
import com.extjs.gxt.ui.client.event.ListViewEvent;
import com.extjs.gxt.ui.client.event.Listener;
import com.extjs.gxt.ui.client.event.SelectionChangedEvent;
import com.extjs.gxt.ui.client.event.SelectionChangedListener;
import com.extjs.gxt.ui.client.store.ListStore;
import com.extjs.gxt.ui.client.widget.ListView;
import com.extjs.gxt.ui.client.widget.layout.FitLayout;


/**
 * This panel shows file details (it uses a grid to display files
 * information)
 * 
 * @author Eric Taix
 */
public class FileList extends AbstractFileUI {
	private ListView<FileModel> view;

	/**
	 * Default constructor
	 */
	public FileList() {
		initUI();
	}

	/**
	 * Initialize the user interface
	 */
	protected void initUI() {
		setId("file-mosaic-id");
		setLayout(new FitLayout());
		setBorders(true);

		view = new ListView<FileModel>();
		view.setId("file-mosaic-view");
		view.setTemplate(getTemplate());
		view.setBorders(false);
		view.setStore(null);
		view.setItemSelector("div.thumb-wrap");
		view.getSelectionModel().setSelectionMode(SelectionMode.MULTI);
		// Handle the selection event
		view.getSelectionModel().addSelectionChangedListener(new SelectionChangedListener<FileModel>() {
			@Override
			public void selectionChanged(SelectionChangedEvent<FileModel> se) {
				FileModel model = view.getSelectionModel().getSelectedItem();
				FilesEvents.fireCurrentFileChanged(model);
			}
		});
		// Handle DoubleClick event
		view.addListener(Events.DoubleClick, new Listener<ListViewEvent>() {
			public void handleEvent(ListViewEvent be) {
				FileModel fileModel = view.getSelectionModel().getSelectedItem();
				if (fileModel != null) {
					// If it's a directory then change the current
					if (fileModel.isDirectory()) {
						FilesEvents.fireDirectoryChanged(getFullPath(fileModel.getName()));
					}
					// It's a file, so fire onSucess event
					else {

					}
				}
			}

		});
		
		add(view);
	}

	private native String getTemplate() /*-{
		return ['<tpl for=".">', 
		'<div class="thumb-wrap" id="{namex}" style="border: 1px solid white">', 
		'', 
		'<div class="x-editable"><img src="{icon16x16}" title="{name}">{name}</div></div>', 
		'</tpl>', 
		'<div class="x-clear"></div>'].join("");
	}-*/;

	public void setStore(ListStore<FileModel> storeP) {
		view.setStore(storeP);
	}

}
