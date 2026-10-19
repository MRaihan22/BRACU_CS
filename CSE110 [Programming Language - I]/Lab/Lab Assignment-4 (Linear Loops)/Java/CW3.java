import java.util.Scanner;

public class CW3 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        while (true)
        {
            System.out.print("Enter number: ");
            int num = sc.nextInt();
            if (num < 0) 
            {
                System.out.println("Program Stopped!");
                break;
            }
            
            System.out.println(num + " ^ 2 = " + (num * num));
        }
    }
}
