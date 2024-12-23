using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace PAWSProject.Models
{
    public class tbladoptformModel
    {
        public int formID { get; set; }
        public int petID { get; set; }
        public string fname { get; set; }
        public string lname { get; set; }
        public string phonenum { get; set; }
        public string email { get; set; }
        public string address { get; set; }
        public DateTime submitAt { get; set; }
    }
}