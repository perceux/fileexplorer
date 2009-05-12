package org.dreamsoft.fileexplorer.client.dialog.ui.detail;

import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.store.ListStore;
import com.extjs.gxt.ui.client.widget.LayoutContainer;

public abstract class AbstractFileUI extends LayoutContainer {
	protected String basePath = "";

	abstract public void setStore(ListStore<FileModel> storeP);

	public void setBasePath(String basePath) {
		this.basePath = basePath;
	}

	public String getFullPath(String fileName) {
		return basePath + ((basePath.endsWith("/") || fileName.startsWith("/"))?"":"/") + fileName;
	}
}
