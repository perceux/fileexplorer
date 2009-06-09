package org.dreamsoft.fileexplorer.client.dialog.ui.detail;

import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.store.ListStore;
import com.extjs.gxt.ui.client.widget.LayoutContainer;

public abstract class AbstractFileUI extends LayoutContainer {

	abstract public void setStore(ListStore<FileModel> storeP);

}
