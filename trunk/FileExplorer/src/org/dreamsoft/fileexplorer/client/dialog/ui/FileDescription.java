/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.ui;

import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.core.XTemplate;
import com.extjs.gxt.ui.client.data.BaseModelData;
import com.extjs.gxt.ui.client.data.ModelData;
import com.extjs.gxt.ui.client.util.Util;
import com.extjs.gxt.ui.client.widget.LayoutContainer;
import com.extjs.gxt.ui.client.widget.layout.FitLayout;
import com.google.gwt.core.client.GWT;

/**
 * The panel which contains files details
 * 
 * @author Eric Taix
 */
public class FileDescription extends LayoutContainer {
	/**
	 * Constructor
	 */
	public FileDescription() {
		initUI();
	}

	/**
	 * Initialiaze the user interface
	 */
	protected void initUI() {
		setBorders(false);
		setLayout(new FitLayout());
	}

	/**
	 * The current selected file has been changed
	 * 
	 * @param currentP
	 */
	public void currentFileChanged(FileModel currentP) {
		if (currentP != null) {
			XTemplate tpl = XTemplate.create(getTemplate());
			removeAll();
			// Create the model
			final ModelData fileModel = new BaseModelData();
			String base = GWT.getHostPageBaseURL();
			fileModel.set("path", base + currentP.getIcon48x48());
			fileModel.set("name", currentP.getName());
			fileModel.set("size", currentP.getSize());
			fileModel.set("dateString", currentP.getLastModified());
			addText(tpl.applyTemplate(Util.getJsObject(fileModel, 3)));
			layout();
		}
	}

	public native String getTemplate() /*-{
		return ['<div class="details">', 
		'<tpl for=".">', 
		'<img src="{path}"><div class="details-info">', 
		'<b>Name:</b>', 
		'<span>{name}</span><br/>', 
		'<b>Size:</b>', 
		'<span>{size}</span><br/>', 
		'<b>Last Modified:</b>', 
		'<span>{dateString}</span><br/></div>', 
		'</tpl>', 
		'</div>'].join("");
	}-*/;
}
