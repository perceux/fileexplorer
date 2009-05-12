package org.dreamsoft.fileexplorer.client.dialog.controler;

import java.util.Iterator;

import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;
import org.dreamsoft.fileexplorer.client.dialog.ui.FileDescription;
import org.dreamsoft.fileexplorer.client.dialog.ui.FilePanel;
import org.dreamsoft.fileexplorer.client.dialog.ui.FilePanel.DisplayType;
import org.dreamsoft.fileexplorer.client.dialog.ui.detail.AbstractFileUI;

import com.extjs.gxt.ui.client.mvc.AppEvent;
import com.extjs.gxt.ui.client.mvc.Controller;
import com.extjs.gxt.ui.client.store.GroupingStore;
import com.extjs.gxt.ui.client.store.ListStore;
import com.extjs.gxt.ui.client.widget.Info;

/**
 * File controler
 * 
 * @author Eric Taix
 */
public class FileControler extends Controller {
	private FilePanel filePanel;
	private FileDescription fileDescription;

	/**
	 * Contructor
	 */
	public FileControler(FilePanel panelP, FileDescription fileDescriptionP) {
		filePanel = panelP;
		fileDescription = fileDescriptionP;
		registerEventTypes(FilesEvents.DIRECTORY_CHANGED, FilesEvents.STORE_CHANGED, FilesEvents.DISPLAY_TYPE_CHANGED, FilesEvents.CURRENT_FILE_CHANGED);
	}

	/**
	 * Handles events fired by the dispatcher
	 */
	public void handleEvent(AppEvent eventP) {
		// The display type have been changed
		if (eventP.getType().equals(FilesEvents.DISPLAY_TYPE_CHANGED)) {
			filePanel.changeDisplay((DisplayType) eventP.getData());
		}
		// Current selected file changed
		else if (eventP.getType().equals(FilesEvents.CURRENT_FILE_CHANGED)) {
			fileDescription.currentFileChanged((FileModel) eventP.getData());
		}
		// Store Content changed
		else if (eventP.getType().equals(FilesEvents.STORE_CHANGED)) {
			Info.display("","Mise à jour des views");
			for (Iterator<AbstractFileUI> iterator = filePanel.getFilesUI().iterator(); iterator.hasNext();) {
				AbstractFileUI fileUI = iterator.next();
				fileUI.setStore((ListStore<FileModel>) eventP.getData());
			}
		}
		// Directory changed
		else if (eventP.getType().equals(FilesEvents.DIRECTORY_CHANGED)) {
			for (Iterator<AbstractFileUI> iterator = filePanel.getFilesUI().iterator(); iterator.hasNext();) {
				AbstractFileUI fileUI = iterator.next();
				fileUI.setBasePath((String) eventP.getData());
			}
		}
	}

}
