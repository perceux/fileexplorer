package org.dreamsoft.fileexplorer.client.dialog.ui;

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

public class DirectoryPanel extends ContentPanel {

	private String url;
	private Tree dirs = new Tree();
	private TreeJsonStore<FileModel> store;
	private FileListLoadConfig loadConfig = new FileListLoadConfig();
	private ScriptTagProxy<ListLoadResult<FileModel>> proxy = new ScriptTagProxy<ListLoadResult<FileModel>>("") {

		protected String generateUrl(Object loadConfig) {
			String s = super.generateUrl(loadConfig);
			InfoConfig ifg = new InfoConfig(" tree url", url + "<br>" + s);
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
		
//		binder.setIconProvider(new ModelStringProvider<FileModel>() {
//			public String getStringValue(FileModel model, String property) {
//				if (!(model.isDirectory())) {
//					String ext = model.getExtension();
//					// new feature, using image paths rather than style names
//					if ("xml".equals(ext)) {
//						return "images/icons/page_white_code.png";
//					} else if ("java".equals(ext)) {
//						return "images/icons/page_white_cup.png";
//					} else if ("html".equals(ext)) {
//						return "images/icons/html.png";
//					} else {
//						return "images/icons/page_white.png";
//					}
//				}
//				return null;
//			}
//
//		});
		binder.setDisplayProperty("name");
		
		loadConfig.setSource("/dev");
		store.getStore().getLoader().load(loadConfig);
		add(dirs);
	}

	private void initStore() {
        //ModelType mt = new ModelType(); 
       // mt.setTotalName("TotalRecords"); 
      //  mt.setRoot("Records");
//        
//        mt.addField("id"); 
//        mt.addField("name"); 
//        mt.addField("text"); 
//        mt.addField("iconCls"); 
//        mt.addField("expanded"); 
//        mt.addField("haveChildren"); 

		ModelType mt = new ModelType();
		mt.setRoot("files");
		mt.setTotalName("total");
		mt.addField("name");
		mt.addField("type");
        
        store = new TreeJsonStore<FileModel>(mt,"http://localhost:8080/php/file.php", proxy);
        
        dirs = new Tree();
        dirs.getStyle().setLeafIconStyle("icon-music");

        TreeBinder<FileModel> binder = new TreeBinder<FileModel>(dirs, store.getStore()); 
        binder.setDisplayProperty("text");
        //BaseListLoadConfig config = new BaseListLoadConfig();
        loadConfig.setAllowNestedValues(false);
        loadConfig.set("request", "tree");
	}
	
}
