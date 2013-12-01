#include <iostream>
#include <opencv2/core/core.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/calib3d/calib3d.hpp>
#include <wildlife_graphics.hpp>

#include <util.h>
#include <diagnostics.h>
#include <boinc_api.h>
#include <graphics2.h>

using namespace std;

//TEXTURE_DESC logo;
APP_INIT_DATA uc_aid;
UC_SHMEM* shmem = NULL;

int width, height;
int mouse_x, mouse_y;
bool mouse_down;

void draw_logo() {
    //if (logo.present) {
        cerr << "Logo is present" << endl;
    //}
}

void app_graphics_init() {
    // Load logo from disk here and initialize the viewport
}

void app_graphics_render(int xs, int ys, double time_of_day) {
    if (shmem == NULL) {
        shmem = (UC_SHMEM*)boinc_graphics_get_shmem("wildlife_surf_collect");
    }
    if (shmem) {
        cv::namedWindow("Wildlife@Home", cv::WINDOW_NORMAL);
        cv::imshow("Wildlife@Home", shmem->image);
        cv::updateWindow("Wildlife@Home");
    } else {
        // Check simple timeout function
        boinc_close_window_and_quit("shmem not updated");
    }

    if (shmem->status.suspended) {
        // Show suspended message
    }
}

void app_graphics_resize(int x, int y) {
    width = x;
    height = y;
}

void boinc_app_mouse_move(int x, int y, int left, int middle, int right) {}
void boinc_app_mouse_button(int x, int y, int which, int is_down) {}
void boinc_app_key_press(int which, int is_down) {}
void boinc_app_key_release(int which, int is_down) {}

int main(int argc, char** argv) {                                               
   boinc_init_graphics_diagnostics(BOINC_DIAG_DEFAULTS);                       
#ifdef __APPLE__                                                                
    //setMacIcon(argv[0], MacAppIconData, sizeof(MacAppIconData));                
#endif                                                                          
    boinc_parse_init_data_file();                                               
    boinc_get_init_data(uc_aid);                                                
    if (uc_aid.project_preferences) {                                           
//        parse_project_prefs(uc_aid.project_preferences);                        
    }                                                                           
    boinc_graphics_loop(argc, argv);                                            
    boinc_finish_diag();                                                        
}
