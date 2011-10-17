package org.dreamsoft.fileexplorer.client.dialog.ui;

import org.dreamsoft.fileexplorer.client.dialog.SiteManager;
import org.dreamsoft.fileexplorer.client.dialog.controler.FileListLoadConfig;
import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.Style.Scroll;
import com.extjs.gxt.ui.client.binder.TreeBinder;
import com.extjs.gxt.ui.client.data.ListLoadResult;
import com.extjs.gxt.ui.client.data.ModelType;
import com.extjs.gxt.ui.client.data.ScriptTagProxy;
import com.extjs.gxt.ui.client.event.IconButtonEvent;
import com.extjs.gxt.ui.client.event.SelectionListener;
import com.extjs.gxt.ui.client.widget.ContentPanel;
import com.extjs.gxt.ui.client.widget.Info;
import com.extjs.gxt.ui.client.widget.InfoConfig;
import com.extjs.gxt.ui.client.widget.button.ToolButton;
import com.extjs.gxt.ui.client.widget.layout.FitLayout;
import com.extjs.gxt.ui.client.widget.tree.Tree;
import com.google.gwt.core.client.GWT;

public class DirectoryPanel extends ContentPanel {

	private String url;

	private Tree dirs = new Tree();

	private TreeJsonStore<FileModel> store;

	private FileListLoadConfig loadConfig = new FileListLoadConfig();

	private ScriptTagProxy<ListLoadResult<FileModel>> proxy = new ScriptTagProxy<ListLoadResult<FileModel>>("") {

		protected String generateUrl(Object loadConfig) {
			String s = super.generateUrl(loadConfig);
			InfoConfig ifg = new InfoConfig(" tree url", url + "<br>" + s);
			GWT.log(url + s, null);
			ifg.width = 600;
			ifg.display = 4000;
			Info.display(ifg);
			return s;
		}

	};

	public DirectoryPanel(String url) {
		this.url = url;
		setBorders(true);
		setBodyBorder(false);
		setLayout(new FitLayout());
		setHeading("Directory");
		setScrollMode(Scroll.AUTO);
		getHeader().addTool(new ToolButton("x-tool-refresh", new SelectionListener<IconButtonEvent>() {
			public void componentSelected(IconButtonEvent ce) {
				loadConfig.setSource("/dev");
				store.getStore().getLoader().load(loadConfig);
			};
		}));
		initStore();
		/*
		 * for (Iterator<SiteModel> iterator =
		 * SiteManager.getSites().iterator(); iterator.hasNext();) { SiteModel
		 * site = iterator.next(); TreeItem ti = new TreeItem(site.getName());
		 * 
		 * }
		 */
		TreeBinder<FileModel> binder = new TreeBinder<FileModel>(dirs, store.getStore());
		binder.setDisplayProperty("name");

		loadConfig.setSource("/dev");
		store.getStore().getLoader().load(loadConfig);
		add(dirs);
	}

	private void initStore() {
		// mt.addField("id");
		// mt.addField("name");
		// mt.addField("text");
		// mt.addField("iconCls");
		// mt.addField("expanded");
		// mt.addField("haveChildren");

		ModelType mt = new ModelType();
		mt.setRoot("files");
		mt.setTotalName("total");
		mt.addField("name");
		mt.addField("type");
		
		
		store = new TreeJsonStore<FileModel>(mt, SiteManager.getLocalUrl() + "/php/file.php", proxy);

		dirs = new Tree();
		dirs.getStyle().setLeafIconStyle("icon-music");

		TreeBinder<FileModel> binder = new TreeBinder<FileModel>(dirs, store.getStore());
		binder.setDisplayProperty("text");
		loadConfig.setAllowNestedValues(false);
		loadConfig.set("request", "tree");
	}

}
