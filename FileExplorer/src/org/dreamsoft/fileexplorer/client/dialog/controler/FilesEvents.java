/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.controler;

import java.util.List;

import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;
import org.dreamsoft.fileexplorer.client.dialog.ui.FilePanel.DisplayType;

import com.extjs.gxt.ui.client.event.EventType;
import com.extjs.gxt.ui.client.mvc.AppEvent;
import com.extjs.gxt.ui.client.mvc.Dispatcher;
import com.extjs.gxt.ui.client.store.GroupingStore;
import com.extjs.gxt.ui.client.store.ListStore;

/**
 * Events supported by files extension package and facade method to dispatch
 * specific events
 * 
 * @author Eric Taix
 */
public class FilesEvents {

	// The current selected file has been changed
	public static final EventType FILE_CHANGED = new EventType(1);
	// The current selected directory has been changed
	public static final EventType DIRECTORY_CHANGED = new EventType(2);
	// The current display type changed
	public static final EventType DISPLAY_TYPE_CHANGED = new EventType(3);
	// The current selected file has been changed
	public static final EventType CURRENT_FILE_CHANGED = new EventType(4);
	// The current selected site has been changed
	public static final EventType SITE_CHANGED = new EventType(5);
	// Store content change
	public static final EventType STORE_CHANGED = new EventType(6);

	/**
	 * Dispatch an event to inform controlers that the display type has been
	 * changed
	 * 
	 * @param newTypeP
	 */
	public static void fireDisplayTypeChanged(DisplayType newTypeP) {
		Dispatcher dispatcher = Dispatcher.get();
		AppEvent evt = new AppEvent(FilesEvents.DISPLAY_TYPE_CHANGED);
		evt.setData(newTypeP);
		dispatcher.dispatch(evt);
	}

	/**
	 * Dispatch an event to inform controlers that current directory has been
	 * changed
	 * 
	 * @param newDirP
	 */
	public static void fireDirectoryChanged(String newSourceDir) {
		Dispatcher dispatcher = Dispatcher.get();
		AppEvent evt = new AppEvent(FilesEvents.DIRECTORY_CHANGED);
		evt.setData(newSourceDir);
		dispatcher.dispatch(evt);
	}

	/**
	 * Dispatch an event to inform controlers that current directory listing has
	 * been changed
	 * 
	 * @param dirP
	 * @param filesP
	 */
	public static void fireDirectoryListingChanged(FileModel dirP, List<FileModel> filesP) {
		Dispatcher dispatcher = Dispatcher.get();
		AppEvent evt = new AppEvent(FilesEvents.DIRECTORY_CHANGED);
		evt.setData(dirP);
		evt.setData("content", filesP);
		dispatcher.dispatch(evt);
	}

	/**
	 * Dispatch an event to inform controler that the curret selected file has
	 * been changed
	 * 
	 * @param currentP
	 */
	public static void fireCurrentFileChanged(FileModel currentP) {
		Dispatcher dispatcher = Dispatcher.get();
		AppEvent evt = new AppEvent(FilesEvents.CURRENT_FILE_CHANGED);
		evt.setData(currentP);
		dispatcher.dispatch(evt);
	}

	/**
	 * Dispatch an event to inform controler that the current site has been
	 * changed
	 * 
	 * @param currentP
	 */
	public static void fireSiteChanged(String siteBaseUrl) {
		Dispatcher dispatcher = Dispatcher.get();
		AppEvent evt = new AppEvent(FilesEvents.SITE_CHANGED);
		evt.setData(siteBaseUrl);
		dispatcher.dispatch(evt);
	}

	/**
	 * Dispatch an event to inform controler that the store content has been
	 * changed
	 * 
	 * @param store
	 */
	public static void fireStoreChanged(ListStore<FileModel> store) {
		Dispatcher dispatcher = Dispatcher.get();
		AppEvent evt = new AppEvent(FilesEvents.STORE_CHANGED);
		evt.setData(store);
		dispatcher.dispatch(evt);
	}
}