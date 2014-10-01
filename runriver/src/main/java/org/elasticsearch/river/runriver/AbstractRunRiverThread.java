package org.elasticsearch.river.runriver;

//JAVA
import java.io.IOException;
import java.io.File;
import java.util.Map;
import java.util.*;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.InputStreamReader;
import java.io.InputStream;


//ELASTICSEARCH
import org.elasticsearch.client.Client;
import org.elasticsearch.common.inject.Inject;
import org.elasticsearch.river.AbstractRiverComponent;
import org.elasticsearch.river.River;
import org.elasticsearch.river.RiverName;
import org.elasticsearch.river.RiverSettings;
import org.elasticsearch.common.xcontent.support.XContentMapValues;
import org.elasticsearch.common.settings.Settings;
import org.elasticsearch.common.logging.ESLogger;
import org.elasticsearch.common.logging.Loggers;

//ELASTICSEARCH QUERIES
import org.elasticsearch.index.query.QueryBuilders;
import org.elasticsearch.index.query.QueryBuilder;
import org.elasticsearch.index.query.FilterBuilders;

import org.elasticsearch.action.search.SearchRequest;
import org.elasticsearch.search.builder.SearchSourceBuilder;
import org.elasticsearch.search.sort.SortOrder;

import org.elasticsearch.search.facet.FacetBuilders;
import org.elasticsearch.search.facet.termsstats.TermsStatsFacet.*;

//org.json
import net.sf.json.JSONObject;
import net.sf.json.JSONSerializer;
import org.apache.commons.io.IOUtils;

//Jackson https://github.com/FasterXML/jackson-databind/
//import com.fasterxml.jackson.databind.ObjectMapper;
//import com.fasterxml.jackson.databind.JsonNode;



//In java seems to be impossible to inherit/extend from multiple class
//so i need to implement the AbstractRiverComponent compatibility manually
//just to have the logger object. 
//https://github.com/elasticsearch/elasticsearch/blob/master/src/main/java/org/elasticsearch/river/AbstractRiverComponent.java
public class AbstractRunRiverThread extends Thread  {

    //AbstractRiverComponent
    protected final ESLogger logger;
    protected final RiverName riverName;
    protected final RiverSettings settings;
    protected final Client client;

    //Runrivercomponent
    public String es_tribe_host;
    public String es_tribe_cluster;
    public String role;
    public int polling_interval;
    public int fetching_interval;
    public String runNumber;
    public String runIndex_read;
    public String runIndex_write;
    public String boxinfo_write;


    //thread
    public int interval;
    public volatile boolean isRunning;

    //Queries
    SearchRequest open_query;
    SearchRequest boxinfo_query;
    SearchSourceBuilder state_query;
    SearchSourceBuilder stream_query;
    SearchSourceBuilder stream_count;


    @Inject
    public AbstractRunRiverThread(RiverName riverName, RiverSettings settings, Client client) {
        super("RunRiver thread");

        //River Settings
        this.riverName = riverName;
        this.settings = settings;
        this.client = client;
        this.logger = Loggers.getLogger(getClass(), settings.globalSettings(), riverName);

        //RunRiver Settings
        Map<String, Object> rSettings = settings.settings();
        es_tribe_host = XContentMapValues.nodeStringValue(rSettings.get("es_tribe_host"), "es-tribe");
        es_tribe_cluster = XContentMapValues.nodeStringValue(rSettings.get("es_tribe_cluster"), "es-tribe");
        role = XContentMapValues.nodeStringValue(rSettings.get("role"), "monitor");
        polling_interval = XContentMapValues.nodeIntegerValue(rSettings.get("polling_interval"), 30);
        fetching_interval = XContentMapValues.nodeIntegerValue(rSettings.get("fetching_interval"), 5);
        runNumber = XContentMapValues.nodeStringValue(rSettings.get("runNumber"), "0");
        runIndex_read = XContentMapValues.nodeStringValue(rSettings.get("runIndex_read"), "runindex_cdaq_read");
        runIndex_write = XContentMapValues.nodeStringValue(rSettings.get("runIndex_write"), "runindex_cdaq_write");
        boxinfo_write = XContentMapValues.nodeStringValue(rSettings.get("boxinfo_write"), "boxinfo_cdaq_write");
        

        interval = polling_interval;
        
        //Thread settings
        isRunning = true;
    }


    @Override
    public void run() {
        beforeLoop();
        while (isRunning) {

            try {
                mainLoop();
            } catch (IOException e) {
               logger.error("IOEception: ", e);
               selfDelete();
            } catch (Exception e) {
               logger.error("Exception: ", e);
               selfDelete();
            }   
            

            try {
                Thread.sleep(interval * 1000); // needs milliseconds
            } catch (InterruptedException e) {}
        }
        afterLoop();
    }

    public void selfDelete(){
        client.admin().indices().prepareDeleteMapping("_river").setType(riverName.name()).execute();
    }

    public void mainLoop() throws Exception {
        return;
    }
    public void beforeLoop(){
        return;
    }
    public void afterLoop(){
        return;
    }

    public void setRunning(boolean running) {
        isRunning = running;
    }
    
    public RiverName riverName() {
        return riverName;
    }

    public String nodeName() {
        return settings.globalSettings().get("name", "");
    }

    public JSONObject getJson(String queryName) throws Exception {
        String filename = queryName + ".json" ;
        InputStream is = this.getClass().getResourceAsStream( "/json/" + filename );
        String jsonTxt = IOUtils.toString( is );
        JSONObject json = (JSONObject) JSONSerializer.toJSON( jsonTxt );        
        return json;
    }

}
