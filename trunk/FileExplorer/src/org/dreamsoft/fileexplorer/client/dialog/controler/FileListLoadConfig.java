/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.controler;

import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.data.BasePagingLoadConfig;

/**
 * 
 * @author Eric Taix
 * 
 */
@SuppressWarnings("serial")
public class FileListLoadConfig extends BasePagingLoadConfig {

	/**
	 * @param homeDirectoryP
	 *            the homeDirectory to set
	 */
	public void setSource(String source) {
		set("src", source == null ? "" : source);
	}
	/**
	 * @param homeDirectoryP
	 *            the homeDirectory to set
	 */
	public void setDestination(String dest) {
		set("dest", dest == null ? "" : dest);
	}
	/**
	 * @param homeDirectoryP
	 *            the homeDirectory to set
	 */
	public void setCommande(String cmd) {
		set("cmd", cmd == null ? "" : cmd);
	}
}
